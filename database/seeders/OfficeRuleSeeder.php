<?php

namespace Database\Seeders;

use App\Models\AssessmentItem;
use App\Models\OfficeRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing rules to avoid duplicates or legacy strings
        DB::table('office_rules')->truncate();

        // Fetch dynamic items to use as rules for the various offices
        $spreadsheet = AssessmentItem::where('competency_text', 'like', '%spreadsheet%')->first();
        $programming = AssessmentItem::where('competency_text', 'like', '%programming%')->first();
        $troubleshoot = AssessmentItem::where('competency_text', 'like', '%troubleshooting%')->first();
        $cloud = AssessmentItem::where('competency_text', 'like', '%cloud storage%')->first();

        $communication = AssessmentItem::where('competency_text', 'like', '%Communicating ideas%')->first();
        $reports = AssessmentItem::where('competency_text', 'like', '%Preparing clear and accurate reports%')->first();
        $confidentiality = AssessmentItem::where('competency_text', 'like', '%confidential or sensitive%')->first();
        $scheduling = AssessmentItem::where('competency_text', 'like', '%scheduling meetings%')->first();

        $dataEntry = AssessmentItem::where('competency_text', 'like', '%data encoding%')->first();
        $analyzingData = AssessmentItem::where('competency_text', 'like', '%Analyzing data%')->first();
        $attentionDetail = AssessmentItem::where('competency_text', 'like', '%attention to detail%')->first();

        $organizingDocs = AssessmentItem::where('competency_text', 'like', '%Organizing office documents%')->first();
        $filingArchiving = AssessmentItem::where('competency_text', 'like', '%Filing and archiving%')->first();
        $correspondence = AssessmentItem::where('competency_text', 'like', '%correspondence%')->first();
        $wordProcessing = AssessmentItem::where('competency_text', 'like', '%word processing%')->first();

        $rules = [
            // ICT Department (Office ID: 1)
            [
                'office_id' => 1,
                'item' => $spreadsheet,
                'priority' => 5, // Highly Required
            ],
            [
                'office_id' => 1,
                'item' => $programming,
                'priority' => 5, // Highly Required
            ],
            [
                'office_id' => 1,
                'item' => $troubleshoot,
                'priority' => 4, // Important
            ],
            [
                'office_id' => 1,
                'item' => $cloud,
                'priority' => 3, // Moderately Important
            ],

            // Human Resources (Office ID: 2)
            [
                'office_id' => 2,
                'item' => $communication,
                'priority' => 5,
            ],
            [
                'office_id' => 2,
                'item' => $reports,
                'priority' => 4,
            ],
            [
                'office_id' => 2,
                'item' => $confidentiality,
                'priority' => 5,
            ],
            [
                'office_id' => 2,
                'item' => $scheduling,
                'priority' => 3,
            ],

            // Finance & Accounting (Office ID: 3)
            [
                'office_id' => 3,
                'item' => $spreadsheet,
                'priority' => 5,
            ],
            [
                'office_id' => 3,
                'item' => $dataEntry,
                'priority' => 4,
            ],
            [
                'office_id' => 3,
                'item' => $analyzingData,
                'priority' => 5,
            ],
            [
                'office_id' => 3,
                'item' => $attentionDetail,
                'priority' => 4,
            ],

            // Administrative Services (Office ID: 4)
            [
                'office_id' => 4,
                'item' => $organizingDocs,
                'priority' => 5,
            ],
            [
                'office_id' => 4,
                'item' => $filingArchiving,
                'priority' => 4,
            ],
            [
                'office_id' => 4,
                'item' => $correspondence,
                'priority' => 5,
            ],
            [
                'office_id' => 4,
                'item' => $wordProcessing,
                'priority' => 4,
            ],
        ];

        foreach ($rules as $rule) {
            if ($rule['item']) {
                OfficeRule::create([
                    'office_id' => $rule['office_id'],
                    'require_rule' => trim(explode('.', $rule['item']->category)[1] ?? 'Skill'),
                    'rule_value' => $rule['item']->competency_text,
                    'priority' => $rule['priority'],
                ]);
            }
        }
    }
}
