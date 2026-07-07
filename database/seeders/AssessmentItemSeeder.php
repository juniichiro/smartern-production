<?php

namespace Database\Seeders;

use App\Models\AssessmentItem;
use Illuminate\Database\Seeder;

class AssessmentItemSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $categories = [
            'I. Digital and Technical Skills' => [
                'Using word processing software for document creation (MS Word, Google Docs, LibreOffice, etc.)',
                'Using spreadsheet software for organizing, computing, and analyzing data (MS Excel, Google Sheets, etc.)',
                'Creating presentations using digital tools (MS PowerPoint, Canva, Google Slides, etc.)',
                'Performing data encoding or data entry tasks accurately',
                'Managing digital files and folders using cloud storage (Google Drive, OneDrive, etc.)',
                'Using email and digital communication platforms professionally (Gmail, Outlook, etc.)',
                'Performing basic troubleshooting of computer hardware or software problems',
                'Applying basic programming or system-related knowledge (Python, HTML, PHP, etc.)',
                'Searching and retrieving relevant information efficiently using online resources',
                'Using video conferencing or online collaboration tools (Zoom, Google Meet, MS Teams, etc.)',
            ],
            'II. Administrative and Office Skills' => [
                'Organizing office documents and records systematically',
                'Filing and archiving both physical and digital documents',
                'Preparing clear and accurate reports and summaries',
                'Managing and updating office records and documentation',
                'Handling confidential or sensitive information responsibly',
                'Assisting in scheduling meetings and coordinating tasks',
                'Applying attention to detail in reviewing and encoding documents',
                'Following office procedures and standard operational guidelines',
                'Completing assigned administrative tasks accurately and on time',
                'Drafting and preparing official correspondence (letters, memos, forms)',
            ],
            'III. Analytical and Problem-Solving Skills' => [
                'Analyzing data or information relevant to assigned work tasks',
                'Identifying problems or irregularities in work processes or documents',
                'Proposing logical and practical solutions to work-related problems',
                'Conducting basic research and gathering relevant information',
                'Interpreting data, reports, or reference materials accurately',
                'Identifying patterns, trends, or inconsistencies in information',
                'Evaluating and cross-checking information from multiple sources',
                'Organizing and presenting information clearly for analysis or reporting',
                'Making sound logical decisions based on available data',
                'Learning and adapting to new tools, processes, or systems quickly',
            ],
            'IV. Communication and Interpersonal Skills' => [
                'Communicating ideas and information clearly in verbal discussions',
                'Writing clear, organized, and professional reports or messages',
                'Listening carefully and following instructions accurately',
                'Collaborating effectively and respectfully with team members',
                'Maintaining professionalism and proper conduct in workplace interactions',
                'Assisting clients, visitors, or stakeholders politely and appropriately',
                'Participating actively and constructively in team discussions',
                'Offering suggestions or ideas during meetings or group tasks',
                'Resolving misunderstandings or conflicts in a calm and professional manner',
                'Adapting communication style appropriately to different audiences or situations',
            ],
            'V. Work Attitude and Professional Values' => [
                'Demonstrating initiative and working independently when needed',
                'Managing time effectively and meeting assigned deadlines',
                'Showing reliability and consistency in performing assigned tasks',
                'Maintaining a positive and cooperative attitude in the workplace',
                'Accepting feedback and applying corrections constructively',
                'Respecting workplace rules, policies, and authority',
                'Exercising proper data privacy and information security practices',
                'Demonstrating willingness to take on additional responsibilities when needed',
                'Showing punctuality and regular attendance throughout the internship',
                'Representing the internship program with integrity and professionalism',
            ],
        ];

        foreach ($categories as $category => $items) {
            foreach ($items as $index => $text) {
                AssessmentItem::create([
                    'category' => $category,
                    'competency_text' => $text,
                    'order_index' => $index,
                    'is_active' => true,
                ]);
            }
        }
    }
}
