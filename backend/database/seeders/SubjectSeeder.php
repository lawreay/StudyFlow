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
                $this->lesson('OSI Model', 'Learn how network communication is organised into useful layers.', [
                    ['heading' => 'The big idea', 'body' => 'Sending a message across a network is a team job. The OSI model separates that job into seven layers so each layer has one clear responsibility.'],
                    ['heading' => 'A simple story', 'body' => 'Think of sending a parcel. You write the message, package it, choose a route, and finally put it on the road. Network layers do similar jobs with data.'],
                    ['heading' => 'Mission clue', 'body' => 'When a question mentions routing between networks, look for the Network layer.'],
                ]),
                $this->lesson('TCP/IP', 'Understand the protocol family that helps the internet move data reliably.', [
                    ['heading' => 'The big idea', 'body' => 'TCP/IP is the practical language used by devices on the internet. IP finds the destination; TCP helps make sure important data arrives correctly.'],
                    ['heading' => 'A simple story', 'body' => 'IP is like an address on a parcel. TCP is the delivery checklist that notices a missing page and asks for it again.'],
                    ['heading' => 'Mission clue', 'body' => 'Use TCP when reliability matters. UDP skips delivery checks when speed matters more, such as live audio or games.'],
                ]),
                $this->lesson('Network Devices', 'Meet the devices that move data around a local network and beyond.', [
                    ['heading' => 'The big idea', 'body' => 'A switch connects devices inside one local network. A router moves traffic between different networks.'],
                    ['heading' => 'A simple story', 'body' => 'A switch is the hallway coordinator inside a school. A router is the road planner that sends learners to another campus.'],
                    ['heading' => 'Mission clue', 'body' => 'If traffic needs to leave its current network, the router is the important device.'],
                ]),
            ],
            'Programming' => [
                $this->lesson('Variables', 'Store information so a program can use it later.', [
                    ['heading' => 'The big idea', 'body' => 'A variable is a named container for a value. It helps a program remember things like a score, a name, or the number of lives left.'],
                    ['heading' => 'A simple story', 'body' => 'Imagine labelled jars in a kitchen. A jar named score should hold a number, while a jar named playerName can hold text.'],
                    ['heading' => 'Mission clue', 'body' => 'Choose names that explain what the value means. Clear names make code easier to repair.'],
                ]),
                $this->lesson('Functions', 'Use reusable instructions instead of repeating the same code.', [
                    ['heading' => 'The big idea', 'body' => 'A function groups a job into one reusable block. You define the job once and call it whenever you need it.'],
                    ['heading' => 'A simple story', 'body' => 'A smoothie recipe is a function: give it ingredients, follow the steps, and get a result without rewriting the recipe every time.'],
                    ['heading' => 'Mission clue', 'body' => 'Parameters are inputs. They let one function handle different values without being copied.'],
                ]),
                $this->lesson('Algorithms', 'Turn a problem into clear, repeatable steps.', [
                    ['heading' => 'The big idea', 'body' => 'An algorithm is a step-by-step plan for solving a problem. A good plan is clear enough for another person or a computer to follow.'],
                    ['heading' => 'A simple story', 'body' => 'Making tea is an algorithm: boil water, add the tea, wait, then pour. Changing the order changes the result.'],
                    ['heading' => 'Mission clue', 'body' => 'Sorting is an algorithmic task because it arranges values into a useful order.'],
                ]),
            ],
            'Hardware' => [
                $this->lesson('CPU', 'Understand the part of the computer that carries out instructions.', [
                    ['heading' => 'The big idea', 'body' => 'The CPU is often called the computer’s brain because it executes instructions and coordinates tasks.'],
                    ['heading' => 'A simple story', 'body' => 'The CPU is a fast kitchen chef: it reads each instruction, performs the action, then immediately takes the next order.'],
                    ['heading' => 'Mission clue', 'body' => 'The CPU processes instructions. It does not permanently store files or display the image on your screen.'],
                ]),
                $this->lesson('RAM', 'See why active programs need fast temporary working memory.', [
                    ['heading' => 'The big idea', 'body' => 'RAM holds the information a computer is actively using. It is fast, but it forgets everything when power is removed.'],
                    ['heading' => 'A simple story', 'body' => 'RAM is your study desk. You keep current books open there, but clear the desk when the session ends.'],
                    ['heading' => 'Mission clue', 'body' => 'RAM is temporary and volatile. An SSD is for keeping files after shutdown.'],
                ]),
                $this->lesson('Storage', 'Learn how a computer keeps files when it is switched off.', [
                    ['heading' => 'The big idea', 'body' => 'Storage keeps the operating system, applications, and files for the long term. SSDs and hard drives are common storage devices.'],
                    ['heading' => 'A simple story', 'body' => 'If RAM is your desk, storage is the filing cabinet where work stays safe after you leave.'],
                    ['heading' => 'Mission clue', 'body' => 'Non-volatile storage keeps its data without power.'],
                ]),
            ],
        ];

        foreach ($subjects as $subjectName => $topics) {
            $subject = Subject::updateOrCreate(['name' => $subjectName], [
                'name' => $subjectName,
                'description' => 'Sample curriculum for '.$subjectName,
            ]);

            foreach ($topics as $topic) {
                Topic::updateOrCreate(['subject_id' => $subject->id, 'name' => $topic['name']], [
                    'subject_id' => $subject->id,
                    'name' => $topic['name'],
                    'description' => 'Topics related to '.$topic['name'],
                    'lesson_title' => $topic['lesson_title'],
                    'lesson_summary' => $topic['lesson_summary'],
                    'lesson_content' => $topic['lesson_content'],
                    'lesson_practice' => $topic['lesson_practice'],
                ]);
            }
        }
    }

    private function lesson(string $name, string $summary, array $content): array
    {
        return [
            'name' => $name,
            'lesson_title' => $name.': your first mission',
            'lesson_summary' => $summary,
            'lesson_content' => $content,
            'lesson_practice' => $this->practiceFor($name),
        ];
    }

    private function practiceFor(string $topicName): array
    {
        return match ($topicName) {
            'OSI Model' => $this->practice(
                'A packet needs to be routed between two networks. Which OSI layer handles that job?',
                ['application' => 'Application', 'network' => 'Network', 'physical' => 'Physical'],
                'network',
                'Exactly. The Network layer is responsible for logical addressing and routing.',
                'Think about which layer decides how a packet moves between networks.',
            ),
            'TCP/IP' => $this->practice(
                'Which protocol is designed to make sure important data arrives reliably?',
                ['udp' => 'UDP', 'tcp' => 'TCP', 'ip' => 'IP'],
                'tcp',
                'Correct. TCP checks delivery and helps recover missing data.',
                'Look for the protocol that checks whether data arrived safely.',
            ),
            'Network Devices' => $this->practice(
                'Your packet must travel to a different network. Which device should guide it onward?',
                ['switch' => 'Switch', 'router' => 'Router', 'keyboard' => 'Keyboard'],
                'router',
                'Correct. A router forwards traffic between separate networks.',
                'A switch works inside a local network; you need the device that chooses paths between networks.',
            ),
            'Variables' => $this->practice(
                'Which value makes the most sense for a variable named score?',
                ['number' => '42', 'text' => 'blue', 'function' => 'A function'],
                'number',
                'Correct. Scores are normally numbers so a program can calculate with them.',
                'Think about the type of value a program needs to add, compare, or update as a score.',
            ),
            'Functions' => $this->practice(
                'What is the best reason to place repeated instructions inside a function?',
                ['reuse' => 'To reuse the same job without copying code', 'delete' => 'To delete variables', 'style' => 'To change a page colour'],
                'reuse',
                'Correct. Functions package a job so it can be used again.',
                'Think about avoiding copy-and-paste when the same task appears more than once.',
            ),
            'Algorithms' => $this->practice(
                'Which description best matches an algorithm?',
                ['plan' => 'A clear sequence of steps to solve a problem', 'device' => 'A piece of computer hardware', 'colour' => 'A visual design choice'],
                'plan',
                'Correct. An algorithm is a step-by-step plan for a problem.',
                'Focus on the idea of a repeatable set of instructions.',
            ),
            'CPU' => $this->practice(
                'What is the CPU mainly responsible for?',
                ['execute' => 'Executing instructions', 'storage' => 'Keeping files forever', 'wifi' => 'Connecting directly to Wi-Fi'],
                'execute',
                'Correct. The CPU carries out instructions for programs.',
                'Think about the computer component that performs calculations and logic.',
            ),
            'RAM' => $this->practice(
                'What happens to information in RAM when a computer loses power?',
                ['lost' => 'It is lost', 'permanent' => 'It becomes permanent', 'copied' => 'It is copied to storage automatically'],
                'lost',
                'Correct. RAM is temporary, volatile working memory.',
                'RAM is like a desk for current work, not a long-term filing cabinet.',
            ),
            'Storage' => $this->practice(
                'Which component keeps files available after a computer is switched off?',
                ['ssd' => 'An SSD', 'ram' => 'RAM', 'cache' => 'CPU cache'],
                'ssd',
                'Correct. Storage such as an SSD keeps data without power.',
                'Look for non-volatile, long-term storage.',
            ),
        };
    }

    private function practice(string $question, array $options, string $correctOptionId, string $correctFeedback, string $incorrectFeedback): array
    {
        return [
            'question' => $question,
            'options' => collect($options)->map(
                fn (string $label, string $id) => ['id' => $id, 'label' => $label],
            )->values()->all(),
            'correct_option_id' => $correctOptionId,
            'correct_feedback' => $correctFeedback,
            'incorrect_feedback' => $incorrectFeedback,
        ];
    }
}
