<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fishport_vessel_owners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fishport_vessel_id')
                ->unique()
                ->constrained('fishport_vessels')
                ->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('address', 255);
            $table->string('contact_number', 60);
            $table->string('email', 150);
            $table->string('government_id_number', 150);
            $table->string('business_name', 150);
            $table->timestamps();
        });

        Schema::create('fishport_vessel_operators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fishport_vessel_id')
                ->unique()
                ->constrained('fishport_vessels')
                ->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('license_number', 120);
            $table->string('contact_number', 60);
            $table->string('address', 255);
            $table->timestamps();
        });

        Schema::create('fishport_vessel_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fishport_vessel_id')
                ->unique()
                ->constrained('fishport_vessels')
                ->cascadeOnDelete();
            $table->string('certificate_of_ownership_path', 255)->nullable();
            $table->string('previous_registration_path', 255)->nullable();
            $table->string('boat_permit_license_path', 255)->nullable();
            $table->string('engine_receipt_proof_path', 255)->nullable();
            $table->string('valid_id_path', 255)->nullable();
            $table->string('inspection_certificate_path', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('fishport_vessel_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fishport_vessel_id')
                ->unique()
                ->constrained('fishport_vessels')
                ->cascadeOnDelete();
            $table->string('registration_number', 120);
            $table->string('official_number', 120);
            $table->string('plate_permit_number', 120);
            $table->string('home_port', 150);
            $table->decimal('gross_tonnage', 10, 2);
            $table->decimal('net_tonnage', 10, 2);
            $table->decimal('vessel_length', 10, 2);
            $table->decimal('beam_width', 10, 2);
            $table->decimal('vessel_depth', 10, 2);
            $table->string('engine_type', 120);
            $table->decimal('engine_horsepower', 10, 2);
            $table->string('hull_material', 120);
            $table->string('color_markings', 150);
            $table->unsignedSmallInteger('year_built');
            $table->date('registration_date');
            $table->date('expiration_date');
            $table->string('registration_status', 60);
            $table->date('renewal_date');
            $table->string('issued_by', 150);
            $table->boolean('supporting_documents_uploaded')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        DB::table('fishport_vessels')
            ->orderBy('id')
            ->chunkById(200, static function ($rows): void {
                $owners = [];
                $operators = [];
                $documents = [];
                $registrations = [];

                foreach ($rows as $vessel) {
                    $owners[] = [
                        'fishport_vessel_id' => $vessel->id,
                        'full_name' => $vessel->owner_name ?? '',
                        'address' => $vessel->owner_address ?? '',
                        'contact_number' => $vessel->owner_contact_number ?? '',
                        'email' => $vessel->owner_email ?? '',
                        'government_id_number' => $vessel->owner_government_id_number ?? '',
                        'business_name' => $vessel->business_name ?? '',
                        'created_at' => $vessel->created_at ?? now(),
                        'updated_at' => $vessel->updated_at ?? now(),
                    ];

                    $operators[] = [
                        'fishport_vessel_id' => $vessel->id,
                        'name' => $vessel->captain_operator_name ?? '',
                        'license_number' => $vessel->captain_license_number ?? '',
                        'contact_number' => $vessel->captain_contact_number ?? '',
                        'address' => $vessel->captain_address ?? '',
                        'created_at' => $vessel->created_at ?? now(),
                        'updated_at' => $vessel->updated_at ?? now(),
                    ];

                    $documents[] = [
                        'fishport_vessel_id' => $vessel->id,
                        'certificate_of_ownership_path' => $vessel->certificate_of_ownership_path,
                        'previous_registration_path' => $vessel->previous_registration_path,
                        'boat_permit_license_path' => $vessel->boat_permit_license_path,
                        'engine_receipt_proof_path' => $vessel->engine_receipt_proof_path,
                        'valid_id_path' => $vessel->valid_id_path,
                        'inspection_certificate_path' => $vessel->inspection_certificate_path,
                        'created_at' => $vessel->created_at ?? now(),
                        'updated_at' => $vessel->updated_at ?? now(),
                    ];

                    $hasAllDocs = ! empty($vessel->certificate_of_ownership_path)
                        && ! empty($vessel->previous_registration_path)
                        && ! empty($vessel->boat_permit_license_path)
                        && ! empty($vessel->engine_receipt_proof_path)
                        && ! empty($vessel->valid_id_path)
                        && ! empty($vessel->inspection_certificate_path);

                    $registrations[] = [
                        'fishport_vessel_id' => $vessel->id,
                        'registration_number' => $vessel->registration_number ?? '',
                        'official_number' => $vessel->official_number ?? '',
                        'plate_permit_number' => $vessel->plate_permit_number ?? '',
                        'home_port' => $vessel->home_port ?? '',
                        'gross_tonnage' => (float) ($vessel->gross_tonnage ?? 0),
                        'net_tonnage' => (float) ($vessel->net_tonnage ?? 0),
                        'vessel_length' => (float) ($vessel->vessel_length ?? 0),
                        'beam_width' => (float) ($vessel->beam_width ?? 0),
                        'vessel_depth' => (float) ($vessel->vessel_depth ?? 0),
                        'engine_type' => $vessel->engine_type ?? '',
                        'engine_horsepower' => (float) ($vessel->engine_horsepower ?? 0),
                        'hull_material' => $vessel->hull_material ?? '',
                        'color_markings' => $vessel->color_markings ?? '',
                        'year_built' => (int) ($vessel->year_built ?? date('Y')),
                        'registration_date' => $vessel->registration_date ?? date('Y-m-d'),
                        'expiration_date' => $vessel->expiration_date ?? date('Y-m-d'),
                        'registration_status' => $vessel->registration_status ?? 'Active',
                        'renewal_date' => $vessel->renewal_date ?? date('Y-m-d'),
                        'issued_by' => $vessel->issued_by ?? 'Fishport Management Office',
                        'supporting_documents_uploaded' => (bool) ($vessel->supporting_documents_uploaded ?? $hasAllDocs),
                        'created_by' => $vessel->created_by,
                        'updated_by' => $vessel->updated_by,
                        'remarks' => $vessel->remarks,
                        'created_at' => $vessel->created_at ?? now(),
                        'updated_at' => $vessel->updated_at ?? now(),
                    ];
                }

                DB::table('fishport_vessel_owners')->upsert(
                    $owners,
                    ['fishport_vessel_id'],
                    ['full_name', 'address', 'contact_number', 'email', 'government_id_number', 'business_name', 'updated_at']
                );

                DB::table('fishport_vessel_operators')->upsert(
                    $operators,
                    ['fishport_vessel_id'],
                    ['name', 'license_number', 'contact_number', 'address', 'updated_at']
                );

                DB::table('fishport_vessel_documents')->upsert(
                    $documents,
                    ['fishport_vessel_id'],
                    [
                        'certificate_of_ownership_path',
                        'previous_registration_path',
                        'boat_permit_license_path',
                        'engine_receipt_proof_path',
                        'valid_id_path',
                        'inspection_certificate_path',
                        'updated_at',
                    ]
                );

                DB::table('fishport_vessel_registrations')->upsert(
                    $registrations,
                    ['fishport_vessel_id'],
                    [
                        'registration_number',
                        'official_number',
                        'plate_permit_number',
                        'home_port',
                        'gross_tonnage',
                        'net_tonnage',
                        'vessel_length',
                        'beam_width',
                        'vessel_depth',
                        'engine_type',
                        'engine_horsepower',
                        'hull_material',
                        'color_markings',
                        'year_built',
                        'registration_date',
                        'expiration_date',
                        'registration_status',
                        'renewal_date',
                        'issued_by',
                        'supporting_documents_uploaded',
                        'created_by',
                        'updated_by',
                        'remarks',
                        'updated_at',
                    ]
                );
            }, 'id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fishport_vessel_registrations');
        Schema::dropIfExists('fishport_vessel_documents');
        Schema::dropIfExists('fishport_vessel_operators');
        Schema::dropIfExists('fishport_vessel_owners');
    }
};
