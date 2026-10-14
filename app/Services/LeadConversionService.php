<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeadConversionService
{
    /**
     * Convert a lead into a Company, Contact, and optionally a Deal.
     *
     * @param  array{
     *     create_company?: bool,
     *     company_id?: int|null,
     *     new_company_name?: string|null,
     *     create_deal?: bool,
     *     deal_title?: string|null,
     *     deal_amount?: float|null,
     *     deal_stage?: string|null,
     *     expected_close_date?: string|null,
     *     assigned_user_id?: int|null
     * }  $options
     */
    public function convert(Lead $lead, array $options = [], ?User $actor = null): array
    {
        if ($lead->isConverted()) {
            throw new InvalidArgumentException('This lead has already been converted.');
        }

        return DB::transaction(function () use ($lead, $options, $actor) {
            $assignedUserId = $options['assigned_user_id'] ?? $lead->assigned_user_id ?? $actor?->id;

            // 1. Resolve or Create Company
            $company = null;
            if (! empty($options['company_id'])) {
                $company = Company::find($options['company_id']);
            } elseif (! empty($options['create_company']) || ! empty($lead->company_name) || ! empty($options['new_company_name'])) {
                $companyName = $options['new_company_name'] ?: ($lead->company_name ?: "{$lead->full_name}'s Organization");
                $company = Company::create([
                    'name' => $companyName,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'status' => 'customer',
                    'assigned_user_id' => $assignedUserId,
                    'notes' => "Created via lead conversion from Lead #{$lead->id} ({$lead->full_name}).",
                ]);
            }

            // 2. Create Contact
            $contact = Contact::create([
                'company_id' => $company?->id,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'job_title' => $lead->job_title,
                'status' => 'active',
                'assigned_user_id' => $assignedUserId,
                'notes' => "Converted from Lead #{$lead->id}.",
            ]);

            // 3. Optionally Create Deal
            $deal = null;
            if (! empty($options['create_deal'])) {
                $dealTitle = $options['deal_title'] ?: ($company ? "{$company->name} - Deal" : "{$lead->full_name} - Opportunity");
                $dealAmount = isset($options['deal_amount']) ? (float) $options['deal_amount'] : (float) $lead->estimated_value;
                $dealStage = $options['deal_stage'] ?? Deal::STAGE_QUALIFICATION;
                $probability = Deal::STAGES[$dealStage]['probability'] ?? 20;

                $deal = Deal::create([
                    'title' => $dealTitle,
                    'company_id' => $company?->id,
                    'contact_id' => $contact->id,
                    'assigned_user_id' => $assignedUserId,
                    'stage' => $dealStage,
                    'amount' => $dealAmount,
                    'probability' => $probability,
                    'expected_close_date' => $options['expected_close_date'] ?? now()->addMonth()->format('Y-m-d'),
                    'deal_type' => 'new_business',
                    'notes' => "Created automatically during conversion of Lead #{$lead->id}.",
                ]);
            }

            // 4. Update Lead Record
            $lead->update([
                'status' => Lead::STATUS_CONVERTED,
                'converted_at' => now(),
                'converted_company_id' => $company?->id,
                'converted_contact_id' => $contact->id,
                'converted_deal_id' => $deal?->id,
            ]);

            // 5. Transfer Lead activities & tasks to the new entities
            $lead->activities()->update([
                'company_id' => $company?->id,
                'contact_id' => $contact->id,
                'deal_id' => $deal?->id,
            ]);

            $lead->tasks()->update([
                'company_id' => $company?->id,
                'contact_id' => $contact->id,
                'deal_id' => $deal?->id,
            ]);

            // 6. Log Conversion Activity
            Activity::create([
                'type' => 'note',
                'subject' => 'Lead Converted to Account & Contact',
                'description' => "Lead {$lead->full_name} was converted."
                    .($company ? " Company: {$company->name}." : '')
                    ." Contact: {$contact->full_name}."
                    .($deal ? " Deal: {$deal->title} (\${$deal->amount})." : ''),
                'activity_date' => now(),
                'status' => 'completed',
                'priority' => 'normal',
                'user_id' => $actor?->id ?? $assignedUserId ?? 1,
                'company_id' => $company?->id,
                'contact_id' => $contact->id,
                'lead_id' => $lead->id,
                'deal_id' => $deal?->id,
            ]);

            return [
                'company' => $company,
                'contact' => $contact,
                'deal' => $deal,
                'lead' => $lead->fresh(),
            ];
        });
    }
}
