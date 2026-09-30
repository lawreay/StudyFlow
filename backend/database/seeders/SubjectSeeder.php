<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Computer Networking' => [
                'OSI Model',
                'TCP/IP',
                'Network Devices',
            ],
            'Programming' => [
                'Variables',
                'Functions',
                'Algorithms',
            ],
            'Hardware' => [
                'CPU',
                'RAM',
                'Storage',
            ],
        ];

        foreach ($subjects as $subjectName => $topics) {
            $subject = Subject::create([
                'name' => $subjectName,
                'description' => 'Sample curriculum for ' . $subjectName,
            ]);

            foreach ($topics as $topicName) {
                Topic::create([
                    'subject_id' => $subject->id,
                    'name' => $topicName,
                    'description' => 'Topics related to ' . $topicName,
                ]);
            }
        }
    }
}
