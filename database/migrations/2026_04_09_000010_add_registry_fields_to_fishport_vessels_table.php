<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fishport_vessels', function (Blueprint $table): void {
            $table->string('registration_number', 120)->nullable()->after('vessel_type');
            $table->string('official_number', 120)->nullable()->after('registration_number');
            $table->string('plate_permit_number', 120)->nullable()->after('official_number');
            $table->string('home_port', 150)->nullable()->after('plate_permit_number');
            $table->decimal('gross_tonnage', 10, 2)->nullable()->after('home_port');
            $table->decimal('net_tonnage', 10, 2)->nullable()->after('gross_tonnage');
            $table->decimal('vessel_length', 10, 2)->nullable()->after('net_tonnage');
            $table->decimal('beam_width', 10, 2)->nullable()->after('vessel_length');
            $table->decimal('vessel_depth', 10, 2)->nullable()->after('beam_width');
            $table->string('engine_type', 120)->nullable()->after('vessel_depth');
            $table->decimal('engine_horsepower', 10, 2)->nullable()->after('engine_type');
            $table->string('hull_material', 120)->nullable()->after('engine_horsepower');
            $table->string('color_markings', 150)->nullable()->after('hull_material');
            $table->unsignedSmallInteger('year_built')->nullable()->after('color_markings');

            $table->string('owner_address', 255)->nullable()->after('year_built');
            $table->string('owner_contact_number', 60)->nullable()->after('owner_address');
            $table->string('owner_email', 150)->nullable()->after('owner_contact_number');
            $table->string('owner_government_id_number', 150)->nullable()->after('owner_email');
            $table->string('business_name', 150)->nullable()->after('owner_government_id_number');

            $table->string('captain_operator_name', 150)->nullable()->after('business_name');
            $table->string('captain_license_number', 120)->nullable()->after('captain_operator_name');
            $table->string('captain_contact_number', 60)->nullable()->after('captain_license_number');
            $table->string('captain_address', 255)->nullable()->after('captain_contact_number');

            $table->date('registration_date')->nullable()->after('captain_address');
            $table->date('expiration_date')->nullable()->after('registration_date');
            $table->string('registration_status', 60)->nullable()->after('expiration_date');
            $table->date('renewal_date')->nullable()->after('registration_status');
            $table->string('issued_by', 150)->nullable()->after('renewal_date');
            $table->boolean('supporting_documents_uploaded')->default(false)->after('issued_by');

            $table->string('certificate_of_ownership_path', 255)->nullable()->after('supporting_documents_uploaded');
            $table->string('previous_registration_path', 255)->nullable()->after('certificate_of_ownership_path');
            $table->string('boat_permit_license_path', 255)->nullable()->after('previous_registration_path');
            $table->string('engine_receipt_proof_path', 255)->nullable()->after('boat_permit_license_path');
            $table->string('valid_id_path', 255)->nullable()->after('engine_receipt_proof_path');
            $table->string('inspection_certificate_path', 255)->nullable()->after('valid_id_path');

            $table->foreignId('created_by')->nullable()->after('inspection_certificate_path')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable()->after('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishport_vessels', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');

            $table->dropColumn([
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
                'owner_address',
                'owner_contact_number',
                'owner_email',
                'owner_government_id_number',
                'business_name',
                'captain_operator_name',
                'captain_license_number',
                'captain_contact_number',
                'captain_address',
                'registration_date',
                'expiration_date',
                'registration_status',
                'renewal_date',
                'issued_by',
                'supporting_documents_uploaded',
                'certificate_of_ownership_path',
                'previous_registration_path',
                'boat_permit_license_path',
                'engine_receipt_proof_path',
                'valid_id_path',
                'inspection_certificate_path',
                'remarks',
            ]);
        });
    }
};
