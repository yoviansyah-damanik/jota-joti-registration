<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Participant;
use App\Models\TaskSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskSubmission>
 */
class TaskSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'participant_id' => Participant::factory(),
            'jid' => 'YB6'.fake()->regexify('[A-Z]{3}'),
            'jjid' => 'JJ_'.fake()->regexify('[A-Z0-9]{5}'),
            'jota_file_path' => 'tasks/jota/fake-jota.pdf',
            'joti_file_path' => 'tasks/joti/fake-joti.pdf',
            'notes' => fake()->optional()->sentence(),
            'jota_status' => TaskStatus::Pending,
            'joti_status' => TaskStatus::Pending,
            'final_status' => TaskStatus::Pending,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'jota_status' => TaskStatus::Completed,
            'joti_status' => TaskStatus::Completed,
            'final_status' => TaskStatus::Completed,
            'assessed_at' => now(),
        ]);
    }

    public function revision(): static
    {
        return $this->state(fn () => [
            'jota_status' => TaskStatus::Revision,
            'joti_status' => TaskStatus::Completed,
            'final_status' => TaskStatus::Revision,
            'jota_admin_note' => 'Perbaiki format laporan JOTA.',
            'assessed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'jota_status' => TaskStatus::Rejected,
            'joti_status' => TaskStatus::Rejected,
            'final_status' => TaskStatus::Rejected,
            'admin_notes' => 'Tugas tidak sesuai ketentuan.',
            'assessed_at' => now(),
        ]);
    }
}
