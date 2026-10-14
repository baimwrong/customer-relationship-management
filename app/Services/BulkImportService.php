<?php

namespace App\Services;

use App\Models\BulkImport;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class BulkImportService
{
    /**
     * Import records from an uploaded CSV file.
     *
     * @return array{success: bool, imported: int, errors_count: int, log: list<string>, import: BulkImport}
     */
    public function import(UploadedFile $file, string $entityType, User $user): array
    {
        $path = $file->getRealPath();
        $content = file_get_contents($path);

        // Strip UTF-8 BOM if present
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) {
            throw new \InvalidArgumentException('Uploaded CSV file is empty.');
        }

        // Determine delimiter (comma or semicolon)
        $firstLine = $lines[0];
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $headerRow = str_getcsv(array_shift($lines), $delimiter);
        $headers = array_map(function ($h) {
            return Str::snake(trim(strtolower((string) $h)));
        }, $headerRow);

        $totalRows = count($lines);
        $successCount = 0;
        $errorLog = [];

        foreach ($lines as $index => $line) {
            $rowNum = $index + 2; // Account for header row (line 1)
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line, $delimiter);
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = [];
            foreach ($headers as $hIdx => $key) {
                $data[$key] = trim($row[$hIdx] ?? '');
            }

            try {
                match ($entityType) {
                    'leads' => $this->importLeadRow($data, $user),
                    'contacts' => $this->importContactRow($data, $user),
                    'companies' => $this->importCompanyRow($data, $user),
                    'deals' => $this->importDealRow($data, $user),
                    default => throw new \InvalidArgumentException("Unsupported entity type: {$entityType}"),
                };
                $successCount++;
            } catch (\Throwable $e) {
                $errorLog[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        $status = empty($errorLog) ? 'completed' : ($successCount > 0 ? 'partial' : 'failed');

        $bulkImport = BulkImport::create([
            'user_id' => $user->id,
            'entity_type' => $entityType,
            'filename' => $file->getClientOriginalName(),
            'total_rows' => $totalRows,
            'success_count' => $successCount,
            'error_count' => count($errorLog),
            'status' => $status,
            'errors' => $errorLog,
            'summary' => "Imported {$successCount} of {$totalRows} rows successfully with ".count($errorLog).' errors.',
        ]);

        return [
            'success' => $successCount > 0,
            'imported' => $successCount,
            'errors_count' => count($errorLog),
            'log' => $errorLog,
            'import' => $bulkImport,
        ];
    }

    private function importLeadRow(array $data, User $user): Lead
    {
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';

        if (empty($firstName) && ! empty($data['name'])) {
            $parts = explode(' ', $data['name'], 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? 'Lead';
        }

        if (empty($firstName) || empty($lastName)) {
            throw new \InvalidArgumentException('First name and last name are required.');
        }

        $source = strtolower($data['source'] ?? 'website');
        if (! array_key_exists($source, Lead::SOURCES)) {
            $source = 'website';
        }

        $status = strtolower($data['status'] ?? Lead::STATUS_NEW);
        if (! array_key_exists($status, Lead::STATUSES)) {
            $status = Lead::STATUS_NEW;
        }

        return Lead::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company_name' => $data['company_name'] ?? ($data['company'] ?? null),
            'email' => ! empty($data['email']) ? $data['email'] : null,
            'phone' => ! empty($data['phone']) ? $data['phone'] : null,
            'job_title' => $data['job_title'] ?? ($data['title'] ?? null),
            'source' => $source,
            'status' => $status,
            'estimated_value' => (float) ($data['estimated_value'] ?? ($data['value'] ?? 0)),
            'score' => (int) ($data['score'] ?? 50),
            'assigned_user_id' => $this->resolveUserId($data['assigned_user_id'] ?? ($data['assigned_to'] ?? null), $user),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function importContactRow(array $data, User $user): Contact
    {
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';

        if (empty($firstName) && ! empty($data['name'])) {
            $parts = explode(' ', $data['name'], 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? 'Contact';
        }

        if (empty($firstName) || empty($lastName)) {
            throw new \InvalidArgumentException('First name and last name are required.');
        }

        $companyId = null;
        if (! empty($data['company_id'])) {
            $companyId = (int) $data['company_id'];
        } elseif (! empty($data['company_name']) || ! empty($data['company'])) {
            $companyName = $data['company_name'] ?? $data['company'];
            $company = Company::firstOrCreate(
                ['name' => $companyName],
                ['assigned_user_id' => $user->id, 'status' => 'customer']
            );
            $companyId = $company->id;
        }

        return Contact::create([
            'company_id' => $companyId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => ! empty($data['email']) ? $data['email'] : null,
            'phone' => ! empty($data['phone']) ? $data['phone'] : null,
            'mobile' => ! empty($data['mobile']) ? $data['mobile'] : null,
            'job_title' => $data['job_title'] ?? ($data['title'] ?? null),
            'department' => $data['department'] ?? null,
            'status' => strtolower($data['status'] ?? 'active'),
            'assigned_user_id' => $this->resolveUserId($data['assigned_user_id'] ?? ($data['assigned_to'] ?? null), $user),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function importCompanyRow(array $data, User $user): Company
    {
        $name = $data['name'] ?? ($data['company_name'] ?? '');
        if (empty($name)) {
            throw new \InvalidArgumentException('Company name is required.');
        }

        return Company::create([
            'name' => $name,
            'industry' => $data['industry'] ?? null,
            'website' => $data['website'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'postal_code' => $data['postal_code'] ?? ($data['zip'] ?? null),
            'annual_revenue' => ! empty($data['annual_revenue']) ? (float) $data['annual_revenue'] : null,
            'employees_count' => ! empty($data['employees_count']) ? (int) $data['employees_count'] : null,
            'status' => strtolower($data['status'] ?? 'active'),
            'assigned_user_id' => $this->resolveUserId($data['assigned_user_id'] ?? ($data['assigned_to'] ?? null), $user),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function importDealRow(array $data, User $user): Deal
    {
        $title = $data['title'] ?? ($data['deal_name'] ?? '');
        if (empty($title)) {
            throw new \InvalidArgumentException('Deal title is required.');
        }

        $companyId = null;
        if (! empty($data['company_id'])) {
            $companyId = (int) $data['company_id'];
        } elseif (! empty($data['company_name']) || ! empty($data['company'])) {
            $company = Company::firstOrCreate(
                ['name' => $data['company_name'] ?? $data['company']],
                ['assigned_user_id' => $user->id, 'status' => 'customer']
            );
            $companyId = $company->id;
        }

        $stage = strtolower($data['stage'] ?? Deal::STAGE_QUALIFICATION);
        if (! array_key_exists($stage, Deal::STAGES)) {
            $stage = Deal::STAGE_QUALIFICATION;
        }

        $probability = isset($data['probability']) && $data['probability'] !== ''
            ? (int) $data['probability']
            : (Deal::STAGES[$stage]['probability'] ?? 20);

        return Deal::create([
            'title' => $title,
            'company_id' => $companyId,
            'contact_id' => ! empty($data['contact_id']) ? (int) $data['contact_id'] : null,
            'assigned_user_id' => $this->resolveUserId($data['assigned_user_id'] ?? ($data['assigned_to'] ?? null), $user),
            'stage' => $stage,
            'amount' => (float) ($data['amount'] ?? 0),
            'expected_close_date' => ! empty($data['expected_close_date']) ? $data['expected_close_date'] : now()->addMonth()->format('Y-m-d'),
            'probability' => $probability,
            'deal_type' => strtolower($data['deal_type'] ?? 'new_business'),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function resolveUserId(mixed $value, User $defaultUser): int
    {
        if (empty($value)) {
            return $defaultUser->id;
        }

        if (is_numeric($value)) {
            $found = User::find((int) $value);
            if ($found) {
                return $found->id;
            }
        }

        if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $found = User::where('email', $value)->first();
            if ($found) {
                return $found->id;
            }
        }

        return $defaultUser->id;
    }

    /**
     * Generate template CSV content with sample data for downloads.
     */
    public function generateTemplate(string $entityType): string
    {
        return match ($entityType) {
            'leads' => "first_name,last_name,company_name,email,phone,job_title,source,status,estimated_value,score,notes\n"
                ."Jane,Doe,Horizon Innovations,jane.doe@horizon.example.com,+1 555-0199,CTO,website,new,45000,70,Interested in enterprise tier\n"
                ."Arthur,Dent,Deep Space Mining,arthur@space.example.com,+1 555-0144,Operations Director,referral,qualified,90000,85,Referred by partner\n",

            'contacts' => "first_name,last_name,company_name,email,phone,mobile,job_title,department,status,notes\n"
                ."Sarah,Miller,Apex Cloud Systems,s.miller@apex.example.com,+1 555-0121,+1 555-0122,Procurement Lead,Purchasing,active,Lead contract signer\n"
                ."Carlos,Gomez,Vanguard Financial,cgomez@vanguard.example.com,+1 555-0131,,Director of Compliance,Legal,vip,Decision maker for security\n",

            'companies' => "name,industry,website,email,phone,address,city,state,country,postal_code,annual_revenue,employees_count,status,notes\n"
                ."Starlight Solutions,Technology,https://starlight.example.com,info@starlight.example.com,+1 555-0100,77 Market St,San Francisco,CA,United States,94105,5000000,120,active,Key tech partner\n"
                ."Cascade Manufacturing,Manufacturing,https://cascade.example.com,sales@cascade.example.com,+1 555-0200,400 Industrial Pkwy,Seattle,WA,United States,98101,15000000,450,customer,Annual service agreement\n",

            'deals' => "title,company_name,stage,amount,probability,expected_close_date,deal_type,notes\n"
                .'Starlight Cloud Migration,Starlight Solutions,proposal,75000,60,'.now()->addDays(30)->format('Y-m-d').",new_business,Proposal delivered to leadership\n"
                .'Cascade IoT Expansion,Cascade Manufacturing,negotiation,140000,80,'.now()->addDays(20)->format('Y-m-d').",existing_business,Contract revisions in progress\n",

            default => throw new \InvalidArgumentException("No template for {$entityType}"),
        };
    }
}
