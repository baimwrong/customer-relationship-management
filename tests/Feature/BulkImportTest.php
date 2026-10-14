<?php

namespace Tests\Feature;

use App\Models\BulkImport;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_rep_cannot_access_admin_import_hub(): void
    {
        $rep = User::factory()->salesRep()->create();

        $response = $this->actingAs($rep)->get(route('admin.imports.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_import_hub(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.imports.index'));

        $response->assertStatus(200);
        $response->assertSee('Enterprise Bulk Data Import Center');
    }

    public function test_admin_can_download_sample_csv_templates(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['leads', 'contacts', 'companies', 'deals'] as $type) {
            $response = $this->actingAs($admin)->get(route('admin.imports.template', $type));
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        }
    }

    public function test_admin_can_bulk_import_leads_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $csvContent = "first_name,last_name,company_name,email,source,status,estimated_value\n"
            ."John,Wick,Continental Hotels,john@continental.test,website,new,50000\n"
            ."Winston,Scott,Continental NYC,winston@continental.test,referral,qualified,150000\n";

        $file = UploadedFile::fake()->createWithContent('leads.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.imports.process'), [
            'entity_type' => 'leads',
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.imports.index'));
        $this->assertDatabaseHas('leads', ['email' => 'john@continental.test']);
        $this->assertDatabaseHas('leads', ['email' => 'winston@continental.test']);

        $this->assertDatabaseHas('bulk_imports', [
            'entity_type' => 'leads',
            'success_count' => 2,
            'error_count' => 0,
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_bulk_import_companies_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $csvContent = "name,industry,website,city,country,status\n"
            ."Cyberdyne Systems,Technology,https://cyberdyne.test,Los Angeles,United States,active\n"
            ."Weyland Yutani,Aerospace,https://weyland.test,Tokyo,Japan,prospect\n";

        $file = UploadedFile::fake()->createWithContent('companies.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.imports.process'), [
            'entity_type' => 'companies',
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.imports.index'));
        $this->assertDatabaseHas('companies', ['name' => 'Cyberdyne Systems']);
        $this->assertDatabaseHas('companies', ['name' => 'Weyland Yutani']);
    }

    public function test_bulk_import_records_row_errors_without_failing_valid_rows(): void
    {
        $admin = User::factory()->admin()->create();

        // Row 1 has valid lead.
        // Row 2 is missing both first_name and last_name (invalid).
        // Row 3 has valid lead.
        $csvContent = "first_name,last_name,email\n"
            ."Bruce,Wayne,bruce@wayne.test\n"
            .",,invalid@error.test\n"
            ."Clark,Kent,clark@planet.test\n";

        $file = UploadedFile::fake()->createWithContent('mixed_leads.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('admin.imports.process'), [
            'entity_type' => 'leads',
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.imports.index'));
        $this->assertDatabaseHas('leads', ['email' => 'bruce@wayne.test']);
        $this->assertDatabaseHas('leads', ['email' => 'clark@planet.test']);

        $import = BulkImport::latest()->first();
        $this->assertNotNull($import);
        $this->assertEquals(2, $import->success_count);
        $this->assertEquals(1, $import->error_count);
        $this->assertEquals('partial', $import->status);
        $this->assertStringContainsString('Row 3', $import->errors[0]); // Row 3 in file (header is line 1, row 2 is line 3)
    }
}
