<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic enterprise CRM demo data.
     */
    public function run(): void
    {
        // 1. Create Key Users with Fixed Credentials
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.test'],
            [
                'name' => 'Alexander Vance',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'phone' => '+1 (555) 019-2831',
                'job_title' => 'Chief Technology & CRM Admin',
                'department' => 'Executive & IT Operations',
                'is_active' => true,
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@crm.test'],
            [
                'name' => 'Sophia Reynolds',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SALES_MANAGER,
                'phone' => '+1 (555) 018-9921',
                'job_title' => 'VP of Global Enterprise Sales',
                'department' => 'Sales Leadership',
                'is_active' => true,
            ]
        );

        $rep1 = User::firstOrCreate(
            ['email' => 'rep1@crm.test'],
            [
                'name' => 'Marcus Chen',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SALES_REP,
                'phone' => '+1 (555) 014-4412',
                'job_title' => 'Senior Strategic Account Executive',
                'department' => 'Enterprise Accounts',
                'is_active' => true,
            ]
        );

        $rep2 = User::firstOrCreate(
            ['email' => 'rep2@crm.test'],
            [
                'name' => 'Elena Rostova',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SALES_REP,
                'phone' => '+1 (555) 012-7788',
                'job_title' => 'Mid-Market Sales Specialist',
                'department' => 'Commercial Sales',
                'is_active' => true,
            ]
        );

        $reps = [$rep1, $rep2, $manager];

        // 2. Create Realistic Enterprise Companies
        $companiesData = [
            [
                'name' => 'Apex Cloud Systems',
                'industry' => 'Technology',
                'website' => 'https://apexcloud.example.com',
                'email' => 'info@apexcloud.example.com',
                'phone' => '+1 (800) 555-0101',
                'address' => '100 Silicon Way, Suite 400',
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'United States',
                'postal_code' => '94105',
                'annual_revenue' => 12500000.00,
                'employees_count' => 350,
                'status' => 'customer',
            ],
            [
                'name' => 'Vanguard Financial Partners',
                'industry' => 'Finance',
                'website' => 'https://vanguardpartners.example.com',
                'email' => 'contact@vanguardpartners.example.com',
                'phone' => '+1 (212) 555-0199',
                'address' => '45 Wall Street, 18th Floor',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'United States',
                'postal_code' => '10005',
                'annual_revenue' => 45000000.00,
                'employees_count' => 1200,
                'status' => 'customer',
            ],
            [
                'name' => 'BioHealth Diagnostics',
                'industry' => 'Healthcare',
                'website' => 'https://biohealthdiag.example.com',
                'email' => 'procurement@biohealthdiag.example.com',
                'phone' => '+1 (617) 555-0145',
                'address' => '750 Kendall Square',
                'city' => 'Cambridge',
                'state' => 'MA',
                'country' => 'United States',
                'postal_code' => '02142',
                'annual_revenue' => 8900000.00,
                'employees_count' => 220,
                'status' => 'prospect',
            ],
            [
                'name' => 'Nordic Robotics AB',
                'industry' => 'Manufacturing',
                'website' => 'https://nordicrobotics.example.com',
                'email' => 'sales@nordicrobotics.example.com',
                'phone' => '+46 8 555 0123',
                'address' => 'Storgatan 24',
                'city' => 'Stockholm',
                'state' => 'Stockholm',
                'country' => 'Sweden',
                'postal_code' => '114 55',
                'annual_revenue' => 24000000.00,
                'employees_count' => 600,
                'status' => 'active',
            ],
            [
                'name' => 'Evergreen Retail Group',
                'industry' => 'Retail',
                'website' => 'https://evergreenretail.example.com',
                'email' => 'support@evergreenretail.example.com',
                'phone' => '+1 (312) 555-0182',
                'address' => '200 Michigan Avenue',
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'United States',
                'postal_code' => '60601',
                'annual_revenue' => 18000000.00,
                'employees_count' => 850,
                'status' => 'prospect',
            ],
            [
                'name' => 'Aura Logistics Worldwide',
                'industry' => 'Logistics',
                'website' => 'https://auralogistics.example.com',
                'email' => 'operations@auralogistics.example.com',
                'phone' => '+1 (404) 555-0167',
                'address' => '1200 Airport Boulevard',
                'city' => 'Atlanta',
                'state' => 'GA',
                'country' => 'United States',
                'postal_code' => '30320',
                'annual_revenue' => 31000000.00,
                'employees_count' => 940,
                'status' => 'active',
            ],
        ];

        $createdCompanies = [];
        foreach ($companiesData as $idx => $comp) {
            $comp['assigned_user_id'] = $reps[$idx % count($reps)]->id;
            $comp['notes'] = 'Key enterprise account in '.$comp['industry'].' sector.';
            $createdCompanies[] = Company::create($comp);
        }

        // 3. Create Contacts
        $contactsData = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Jenkins',
                'email' => 's.jenkins@apexcloud.example.com',
                'phone' => '+1 (555) 010-1001',
                'mobile' => '+1 (555) 010-1002',
                'job_title' => 'Chief Information Officer',
                'department' => 'Technology',
                'status' => 'active',
                'company_index' => 0,
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Kowalski',
                'email' => 'd.kowalski@apexcloud.example.com',
                'phone' => '+1 (555) 010-1003',
                'job_title' => 'Head of Infrastructure Procurement',
                'department' => 'Procurement',
                'status' => 'active',
                'company_index' => 0,
            ],
            [
                'first_name' => 'Victoria',
                'last_name' => 'Sterling',
                'email' => 'v.sterling@vanguardpartners.example.com',
                'phone' => '+1 (212) 555-0191',
                'job_title' => 'Managing Director of Operations',
                'department' => 'Operations',
                'status' => 'vip',
                'company_index' => 1,
            ],
            [
                'first_name' => 'Dr. Robert',
                'last_name' => 'Langdon',
                'email' => 'rlangdon@biohealthdiag.example.com',
                'phone' => '+1 (617) 555-0146',
                'job_title' => 'VP of Clinical Operations',
                'department' => 'Executive',
                'status' => 'lead',
                'company_index' => 2,
            ],
            [
                'first_name' => 'Astrid',
                'last_name' => 'Lindqvist',
                'email' => 'astrid@nordicrobotics.example.com',
                'phone' => '+46 8 555 0124',
                'job_title' => 'Head of Automation Solutions',
                'department' => 'Engineering',
                'status' => 'active',
                'company_index' => 3,
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Gallagher',
                'email' => 'mgallagher@evergreenretail.example.com',
                'phone' => '+1 (312) 555-0183',
                'job_title' => 'Director of Supply Chain Technology',
                'department' => 'Logistics & Tech',
                'status' => 'lead',
                'company_index' => 4,
            ],
        ];

        $createdContacts = [];
        foreach ($contactsData as $idx => $ct) {
            $company = $createdCompanies[$ct['company_index']];
            unset($ct['company_index']);
            $ct['company_id'] = $company->id;
            $ct['assigned_user_id'] = $company->assigned_user_id;
            $ct['notes'] = 'Key stakeholder for contract negotiations.';
            $createdContacts[] = Contact::create($ct);
        }

        // 4. Create Leads across Funnel
        $leadsData = [
            [
                'first_name' => 'Jonathan',
                'last_name' => 'Hale',
                'company_name' => 'Quantum Dynamics Corp',
                'email' => 'jhale@quantumdynamics.example.com',
                'phone' => '+1 (415) 555-0133',
                'job_title' => 'VP Engineering',
                'source' => 'website',
                'status' => Lead::STATUS_NEW,
                'estimated_value' => 85000.00,
                'score' => 60,
                'notes' => 'Inquired through web contact form regarding Enterprise Cloud migration.',
            ],
            [
                'first_name' => 'Claire',
                'last_name' => 'Dubois',
                'company_name' => 'Lumiere Media International',
                'email' => 'cdubois@lumiere.example.com',
                'phone' => '+33 1 42 68 55 00',
                'job_title' => 'Chief Digital Officer',
                'source' => 'referral',
                'status' => Lead::STATUS_CONTACTED,
                'estimated_value' => 140000.00,
                'score' => 75,
                'notes' => 'Referred by Apex Cloud CTO. Needs CRM and digital workflow automation.',
            ],
            [
                'first_name' => 'Benjamin',
                'last_name' => 'Foster',
                'company_name' => 'Solstice Energy Grid',
                'email' => 'bfoster@solsticeenergy.example.com',
                'phone' => '+1 (303) 555-0177',
                'job_title' => 'Director of Infrastructure',
                'source' => 'event',
                'status' => Lead::STATUS_QUALIFIED,
                'estimated_value' => 220000.00,
                'score' => 90,
                'notes' => 'Met at CleanTech Expo 2026. Ready for formal proposal this quarter.',
            ],
            [
                'first_name' => 'Danielle',
                'last_name' => 'Morales',
                'company_name' => 'Summit Financial Advisors',
                'email' => 'dmorales@summitfin.example.com',
                'phone' => '+1 (206) 555-0155',
                'job_title' => 'Operations Manager',
                'source' => 'advertising',
                'status' => Lead::STATUS_UNQUALIFIED,
                'estimated_value' => 15000.00,
                'score' => 25,
                'notes' => 'Budget too small for enterprise tier; passed to self-serve team.',
            ],
        ];

        $createdLeads = [];
        foreach ($leadsData as $idx => $ld) {
            $ld['assigned_user_id'] = $reps[$idx % count($reps)]->id;
            $createdLeads[] = Lead::create($ld);
        }

        // 5. Create Pipeline Deals across all stages
        $dealsData = [
            [
                'title' => 'Apex Cloud - Multi-Region Infrastructure Upgrade',
                'company_index' => 0,
                'contact_index' => 0,
                'stage' => Deal::STAGE_CLOSED_WON,
                'amount' => 185000.00,
                'expected_close_date' => now()->subDays(10)->format('Y-m-d'),
                'probability' => 100,
                'deal_type' => 'renewal',
                'closed_at' => now()->subDays(10),
                'notes' => 'Contract signed for 3-year term. Implementation kickoff scheduled.',
            ],
            [
                'title' => 'Vanguard Financial - Risk Intelligence Suite',
                'company_index' => 1,
                'contact_index' => 2,
                'stage' => Deal::STAGE_NEGOTIATION,
                'amount' => 320000.00,
                'expected_close_date' => now()->addDays(15)->format('Y-m-d'),
                'probability' => 80,
                'deal_type' => 'new_business',
                'notes' => 'Final legal and procurement review of master service agreement in progress.',
            ],
            [
                'title' => 'BioHealth - Diagnostics Workflow Automation',
                'company_index' => 2,
                'contact_index' => 3,
                'stage' => Deal::STAGE_PROPOSAL,
                'amount' => 125000.00,
                'expected_close_date' => now()->addDays(30)->format('Y-m-d'),
                'probability' => 60,
                'deal_type' => 'new_business',
                'notes' => 'Custom proposal and RFP response submitted to executive steering committee.',
            ],
            [
                'title' => 'Nordic Robotics - Assembly Line Telemetry Hub',
                'company_index' => 3,
                'contact_index' => 4,
                'stage' => Deal::STAGE_NEEDS_ANALYSIS,
                'amount' => 210000.00,
                'expected_close_date' => now()->addDays(45)->format('Y-m-d'),
                'probability' => 40,
                'deal_type' => 'new_business',
                'notes' => 'Discovery workshops completed; calculating ROI metrics with technical team.',
            ],
            [
                'title' => 'Evergreen Retail - Omnichannel Inventory Sync',
                'company_index' => 4,
                'contact_index' => 5,
                'stage' => Deal::STAGE_QUALIFICATION,
                'amount' => 95000.00,
                'expected_close_date' => now()->addDays(60)->format('Y-m-d'),
                'probability' => 20,
                'deal_type' => 'new_business',
                'notes' => 'Initial scoping call held with VP of Logistics.',
            ],
            [
                'title' => 'Aura Logistics - Legacy ERP Connector (Archived)',
                'company_index' => 5,
                'contact_index' => 0,
                'stage' => Deal::STAGE_CLOSED_LOST,
                'amount' => 65000.00,
                'expected_close_date' => now()->subDays(20)->format('Y-m-d'),
                'probability' => 0,
                'deal_type' => 'new_business',
                'loss_reason' => 'Client postponed ERP modernization to fiscal year 2027.',
                'closed_at' => now()->subDays(20),
                'notes' => 'Keep in touch for future quarterly review.',
            ],
        ];

        $createdDeals = [];
        foreach ($dealsData as $idx => $dd) {
            $company = $createdCompanies[$dd['company_index']];
            $contact = $createdContacts[$dd['contact_index']];
            unset($dd['company_index'], $dd['contact_index']);
            $dd['company_id'] = $company->id;
            $dd['contact_id'] = $contact->id;
            $dd['assigned_user_id'] = $company->assigned_user_id;
            $createdDeals[] = Deal::create($dd);
        }

        // 6. Create Activities
        Activity::create([
            'type' => 'meeting',
            'subject' => 'Executive Quarterly Business Review',
            'description' => 'Reviewed Q3 performance milestones, expansion licenses, and roadmap alignment.',
            'activity_date' => now()->subDays(3),
            'duration_minutes' => 60,
            'status' => 'completed',
            'priority' => 'high',
            'user_id' => $rep1->id,
            'company_id' => $createdCompanies[0]->id,
            'contact_id' => $createdContacts[0]->id,
            'deal_id' => $createdDeals[0]->id,
        ]);

        Activity::create([
            'type' => 'call',
            'subject' => 'Contract Term Clause Review',
            'description' => 'Addressed data residency requirement and SOC2 type II compliance addendum.',
            'activity_date' => now()->subDays(1),
            'duration_minutes' => 30,
            'status' => 'completed',
            'priority' => 'urgent',
            'user_id' => $manager->id,
            'company_id' => $createdCompanies[1]->id,
            'contact_id' => $createdContacts[2]->id,
            'deal_id' => $createdDeals[1]->id,
        ]);

        Activity::create([
            'type' => 'email',
            'subject' => 'Detailed Proposal & ROI Calculator Sent',
            'description' => 'Emailed comprehensive solution architecture doc and pricing schedule.',
            'activity_date' => now()->subDays(2),
            'duration_minutes' => 15,
            'status' => 'completed',
            'priority' => 'normal',
            'user_id' => $rep2->id,
            'company_id' => $createdCompanies[2]->id,
            'contact_id' => $createdContacts[3]->id,
            'deal_id' => $createdDeals[2]->id,
        ]);

        Activity::create([
            'type' => 'call',
            'subject' => 'Discovery Call with VP Engineering',
            'description' => 'Discussed cloud modernization timeline and current legacy pain points.',
            'activity_date' => now()->subHours(5),
            'duration_minutes' => 45,
            'status' => 'completed',
            'priority' => 'normal',
            'user_id' => $rep1->id,
            'lead_id' => $createdLeads[0]->id,
        ]);

        // 7. Create Actionable Tasks
        Task::create([
            'title' => 'Follow up on MSA Legal Redlines',
            'description' => 'Connect with General Counsel to confirm liability indemnification cap.',
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'priority' => 'urgent',
            'status' => 'in_progress',
            'assigned_user_id' => $manager->id,
            'company_id' => $createdCompanies[1]->id,
            'deal_id' => $createdDeals[1]->id,
        ]);

        Task::create([
            'title' => 'Schedule Technical Deep-Dive Demo',
            'description' => 'Coordinate with Solutions Architect for live platform demonstration.',
            'due_date' => now()->addDays(4)->format('Y-m-d'),
            'priority' => 'high',
            'status' => 'pending',
            'assigned_user_id' => $rep2->id,
            'company_id' => $createdCompanies[2]->id,
            'deal_id' => $createdDeals[2]->id,
        ]);

        Task::create([
            'title' => 'Send Follow-up Case Studies to Jonathan Hale',
            'description' => 'Send healthcare and biotech enterprise case studies for qualification.',
            'due_date' => now()->addDays(1)->format('Y-m-d'),
            'priority' => 'medium',
            'status' => 'pending',
            'assigned_user_id' => $rep1->id,
            'lead_id' => $createdLeads[0]->id,
        ]);
    }
}
