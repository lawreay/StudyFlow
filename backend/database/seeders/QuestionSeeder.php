<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::with('topics')->get();

        $questions = [
            [
                'subject' => 'Computer Networking',
                'topic' => 'OSI Model',
                'question' => 'Which OSI layer is responsible for routing packets between networks?',
                'options' => ['Application', 'Transport', 'Network', 'Data Link'],
                'correct' => 'Network',
                'explanation' => 'The Network layer handles routing and logical addressing between networks.',
            ],
            [
                'subject' => 'Computer Networking',
                'topic' => 'OSI Model',
                'question' => 'Which layer ensures end-to-end reliable data delivery?',
                'options' => ['Transport', 'Physical', 'Session', 'Presentation'],
                'correct' => 'Transport',
                'explanation' => 'The Transport layer manages connection-oriented reliability and flow control.',
            ],
            [
                'subject' => 'Computer Networking',
                'topic' => 'TCP/IP',
                'question' => 'What does TCP stand for?',
                'options' => ['Transmission Control Protocol', 'Transport Communication Process', 'Transmission Connection Program', 'Transfer Control Packet'],
                'correct' => 'Transmission Control Protocol',
                'explanation' => 'TCP is the protocol responsible for reliable delivery of data.',
            ],
            [
                'subject' => 'Computer Networking',
                'topic' => 'TCP/IP',
                'question' => 'Which protocol is connectionless and faster for real-time traffic?',
                'options' => ['UDP', 'TCP', 'HTTP', 'SMTP'],
                'correct' => 'UDP',
                'explanation' => 'UDP does not require a connection and is commonly used for timing-sensitive traffic.',
            ],
            [
                'subject' => 'Computer Networking',
                'topic' => 'Network Devices',
                'question' => 'Which device connects multiple devices on the same local network?',
                'options' => ['Switch', 'Router', 'Modem', 'Firewall'],
                'correct' => 'Switch',
                'explanation' => 'A switch connects devices inside a local network and forwards frames efficiently.',
            ],
            [
                'subject' => 'Computer Networking',
                'topic' => 'Network Devices',
                'question' => 'Which device forwards traffic between different networks?',
                'options' => ['Router', 'Hub', 'Patch panel', 'Repeater'],
                'correct' => 'Router',
                'explanation' => 'A router determines paths and forwards packets between networks.',
            ],
            [
                'subject' => 'Programming',
                'topic' => 'Variables',
                'question' => 'Which is the correct way to declare a variable that stores a number?',
                'options' => ['let count = 5;', 'count = "5";', 'function count()', 'if count == 5'],
                'correct' => 'let count = 5;',
                'explanation' => 'Variables store values and can be declared using a valid assignment expression.',
            ],
            [
                'subject' => 'Programming',
                'topic' => 'Variables',
                'question' => 'What is the purpose of a variable?',
                'options' => ['To store data', 'To delete files', 'To display a website', 'To compile code'],
                'correct' => 'To store data',
                'explanation' => 'Variables hold values that a program can read and update while running.',
            ],
            [
                'subject' => 'Programming',
                'topic' => 'Functions',
                'question' => 'What is a function in programming?',
                'options' => ['A reusable block of code', 'A database table', 'A type of variable', 'A CSS rule'],
                'correct' => 'A reusable block of code',
                'explanation' => 'Functions let code be organized and reused instead of copied repeatedly.',
            ],
            [
                'subject' => 'Programming',
                'topic' => 'Functions',
                'question' => 'Why use parameters in a function?',
                'options' => ['To pass input values', 'To remove errors', 'To format text only', 'To replace variables'],
                'correct' => 'To pass input values',
                'explanation' => 'Parameters allow a function to receive values needed for the operation.',
            ],
            [
                'subject' => 'Programming',
                'topic' => 'Algorithms',
                'question' => 'Which term best describes an algorithm?',
                'options' => ['A step-by-step solution', 'A hardware component', 'A database query', 'A design color palette'],
                'correct' => 'A step-by-step solution',
                'explanation' => 'An algorithm is a clear sequence of steps used to solve a problem.',
            ],
            [
                'subject' => 'Programming',
                'topic' => 'Algorithms',
                'question' => 'What is the goal of sorting an array?',
                'options' => ['Arrange values in order', 'Encrypt data', 'Delete duplicates automatically', 'Open a file'],
                'correct' => 'Arrange values in order',
                'explanation' => 'Sorting helps organize data for searching and processing.',
            ],
            [
                'subject' => 'Hardware',
                'topic' => 'CPU',
                'question' => 'What does CPU stand for?',
                'options' => ['Central Processing Unit', 'Computer Power Utility', 'Central Program Unit', 'Core Processing Utility'],
                'correct' => 'Central Processing Unit',
                'explanation' => 'The CPU is the primary processor that executes instructions and coordinates computer activity.',
            ],
            [
                'subject' => 'Hardware',
                'topic' => 'CPU',
                'question' => 'What is the main job of the CPU?',
                'options' => ['Execute instructions', 'Store files permanently', 'Display video output', 'Connect to Wi-Fi'],
                'correct' => 'Execute instructions',
                'explanation' => 'The CPU performs calculations and logic operations for software programs.',
            ],
            [
                'subject' => 'Hardware',
                'topic' => 'RAM',
                'question' => 'What is RAM used for?',
                'options' => ['Temporary working memory', 'Permanent file storage', 'Power supply regulation', 'Graphics rendering'],
                'correct' => 'Temporary working memory',
                'explanation' => 'RAM stores data the computer actively uses while running programs.',
            ],
            [
                'subject' => 'Hardware',
                'topic' => 'RAM',
                'question' => 'What happens to data in RAM when the computer turns off?',
                'options' => ['It is lost', 'It becomes permanent', 'It is copied to the CPU', 'It is protected'],
                'correct' => 'It is lost',
                'explanation' => 'RAM is volatile memory and loses data when power is removed.',
            ],
            [
                'subject' => 'Hardware',
                'topic' => 'Storage',
                'question' => 'Which storage type is typically non-volatile?',
                'options' => ['SSD', 'RAM', 'Cache', 'Register'],
                'correct' => 'SSD',
                'explanation' => 'Solid-state drives retain data even when the system is powered off.',
            ],
            [
                'subject' => 'Hardware',
                'topic' => 'Storage',
                'question' => 'Why do computers need storage?',
                'options' => ['To retain information long-term', 'To increase CPU speed', 'To connect to the internet', 'To reduce power use'],
                'correct' => 'To retain information long-term',
                'explanation' => 'Storage keeps the operating system, apps, and files available after shutdown.',
            ],
            [
                'subject' => 'Computer Networking',
                'topic' => 'OSI Model',
                'question' => 'Which layer is closest to the user?',
                'options' => ['Application', 'Transport', 'Physical', 'Network'],
                'correct' => 'Application',
                'explanation' => 'The Application layer is where user-facing software interacts with the network.',
            ],
            [
                'subject' => 'Computer Networking',
                'topic' => 'TCP/IP',
                'question' => 'Which protocol is commonly used for web browsing?',
                'options' => ['HTTP', 'SMTP', 'ARP', 'ICMP'],
                'correct' => 'HTTP',
                'explanation' => 'HTTP is the protocol used to retrieve web pages and resources from servers.',
            ],
            [
                'subject' => 'Programming',
                'topic' => 'Variables',
                'question' => 'Which value is best stored in a variable named score?',
                'options' => ['42', '“blue”', 'A function', 'An array name'],
                'correct' => '42',
                'explanation' => 'A score is usually a numeric value, which fits a variable intended for calculations.',
            ],
            [
                'subject' => 'Hardware',
                'topic' => 'CPU',
                'question' => 'A CPU is often described as the computer’s what?',
                'options' => ['Brain', 'Screen', 'Battery', 'Keyboard'],
                'correct' => 'Brain',
                'explanation' => 'The CPU is often called the brain of the computer because it processes instructions.',
            ],
        ];

        foreach ($questions as $rawQuestion) {
            $subject = $subjects->firstWhere('name', $rawQuestion['subject']);
            $topic = $subject?->topics->firstWhere('name', $rawQuestion['topic']);

            if (! $subject || ! $topic) {
                continue;
            }

            $question = Question::create([
                'subject_id' => $subject->id,
                'topic_id' => $topic->id,
                'question_text' => $rawQuestion['question'],
                'explanation' => $rawQuestion['explanation'],
            ]);

            foreach ($rawQuestion['options'] as $optionText) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $optionText === $rawQuestion['correct'],
                ]);
            }
        }
    }
}
