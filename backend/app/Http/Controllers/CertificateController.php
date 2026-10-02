<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\GameWorld;
use App\Services\GameProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CertificateController extends ApiController
{
    public function index(Request $request, GameProgressService $gameProgressService)
    {
        $certificates = Certificate::where('user_id', $request->user()->id)
            ->latest('issued_at')
            ->get()
            ->map(fn (Certificate $certificate): array => $this->certificateData($certificate));
        $eligibleWorlds = $gameProgressService->worldsFor($request->user())
            ->filter(fn (GameWorld $world): bool => $world->nodes->isNotEmpty()
                && $world->nodes->every(fn ($node): bool => $node->is_completed)
                && ! $certificates->contains('game_world_id', $world->id))
            ->map(fn (GameWorld $world): array => [
                'id' => $world->id,
                'name' => $world->name,
            ])
            ->values();

        return $this->success([
            'certificates' => $certificates,
            'eligible_worlds' => $eligibleWorlds,
        ], 'Certificates loaded');
    }

    public function issue(Request $request, GameWorld $world, GameProgressService $gameProgressService)
    {
        if (! $world->is_active) {
            abort(404);
        }

        $playerWorld = $gameProgressService->worldsFor($request->user())
            ->firstWhere('id', $world->id);

        if ($playerWorld === null || $playerWorld->nodes->isEmpty() || ! $playerWorld->nodes->every(
            fn ($node): bool => $node->is_completed,
        )) {
            throw ValidationException::withMessages([
                'world' => ['Complete every mission in this learning world before claiming its certificate.'],
            ]);
        }

        $certificate = DB::transaction(function () use ($request, $world): Certificate {
            $existingCertificate = Certificate::where('user_id', $request->user()->id)
                ->where('game_world_id', $world->id)
                ->first();

            if ($existingCertificate !== null) {
                return $existingCertificate;
            }

            return Certificate::create([
                'user_id' => $request->user()->id,
                'game_world_id' => $world->id,
                'certificate_number' => $this->certificateNumber(),
                'learner_name' => $request->user()->name,
                'program_name' => $world->name,
                'issued_at' => now(),
            ]);
        });

        return $this->success($this->certificateData($certificate), 'Certificate issued', 201);
    }

    public function verify(string $certificateNumber)
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)->firstOrFail();

        return $this->success([
            'certificate_number' => $certificate->certificate_number,
            'learner_name' => $certificate->learner_name,
            'program_name' => $certificate->program_name,
            'issued_at' => $certificate->issued_at,
            'is_valid' => true,
        ], 'Certificate verified');
    }

    private function certificateData(Certificate $certificate): array
    {
        return [
            'id' => $certificate->id,
            'game_world_id' => $certificate->game_world_id,
            'certificate_number' => $certificate->certificate_number,
            'learner_name' => $certificate->learner_name,
            'program_name' => $certificate->program_name,
            'issued_at' => $certificate->issued_at,
            'verification_path' => "/api/certificates/verify/{$certificate->certificate_number}",
        ];
    }

    private function certificateNumber(): string
    {
        do {
            $certificateNumber = 'SF-'.now()->format('Y').'-'.Str::upper(Str::random(10));
        } while (Certificate::where('certificate_number', $certificateNumber)->exists());

        return $certificateNumber;
    }
}
