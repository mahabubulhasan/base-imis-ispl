<?php
// Last Modified Date: 09-02-2026
// Developed By: Streams Tech Ltd.
// Description: Service class for handling public feedback operations
namespace App\Services\Fsm;

use App\Models\Fsm\Application;
use App\Models\Fsm\Feedback;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicFeedbackService
{
    /**
     * Get application details with customer and service provider information
     *
     * @param int $applicationId
     * @return array|null
     */
    public function getApplicationDetails($applicationId)
    {
        $application = Application::with('service_provider')
            ->where('id', $applicationId)
            ->first();

        if (!$application) {
            return null;
        }

        // Check if feedback already exists
        if ($application->feedback_status || $application->feedback) {
            return [
                'error' => 'Feedback for this application has already been submitted.'
            ];
        }

        return [
            'application_id' => $application->id,
            'customer_name' => $application->customer_name,
            'customer_contact' => $application->customer_contact,
            'customer_gender' => $application->customer_gender,
            'service_provider_name' => $application->service_provider?->company_name,
            'service_provider_contact' => $application->service_provider?->contact_number,
        ];
    }

    /**
     * Check if feedback already exists for this application
     *
     * @param int $applicationId
     * @return bool
     */
    public function checkDuplicateSubmission($applicationId)
    {
        return Feedback::where('application_id', $applicationId)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Check rate limiting - 3 submissions per IP per hour
     *
     * @param string $ipAddress
     * @return bool True if rate limit exceeded
     */
    public function checkRateLimit($ipAddress)
    {
        $cacheKey = 'feedback_rate_limit_' . $ipAddress;
        $submissions = Cache::get($cacheKey, 0);

        if ($submissions >= 3) {
            return true; // Rate limit exceeded
        }

        return false;
    }

    /**
     * Increment rate limit counter for IP
     *
     * @param string $ipAddress
     * @return void
     */
    public function incrementRateLimit($ipAddress)
    {
        $cacheKey = 'feedback_rate_limit_' . $ipAddress;
        $submissions = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $submissions + 1, 3600); // 1 hour cache
    }

    /**
     * Store feedback and update application status
     *
     * @param array $validatedData
     * @return Feedback
     */
    public function storeFeedback(array $validatedData)
    {
        DB::beginTransaction();
        try {
            // Get application to populate service_provider_id
            $application = Application::findOrFail($validatedData['application_id']);

            // Create feedback
            $feedback = new Feedback();
            $feedback->application_id = $validatedData['application_id'];
            $feedback->customer_name = $validatedData['customer_name'];
            $feedback->customer_gender = $validatedData['customer_gender'] ?? null;
            $feedback->customer_number = $validatedData['customer_number'];
            $feedback->customer_address = $validatedData['customer_address'] ?? null;

            // Safety measures (checkboxes as comma-separated)
            $feedback->safety_measures = $validatedData['safety_measures'];

            // Satisfaction ratings (Q2-Q5)
            $feedback->fsm_quality_level = $validatedData['fsm_quality_level'];
            $feedback->service_delivery_efficiency = $validatedData['service_delivery_efficiency'];
            $feedback->service_quality_price = $validatedData['service_quality_price'];

            // Q4 Overall service - convert 1-4 scale to boolean (3-4 = true, 1-2 = false)
            $overallSatisfaction = $validatedData['overall_satisfaction'] ?? $validatedData['fsm_quality_level'];
            $feedback->fsm_service_quality = ($overallSatisfaction >= 3);

            // Q6 Dissatisfaction comments
            $feedback->comments = $validatedData['comments'] ?? null;

            // Q7 Payment mechanism
            $feedback->payment_mechanism_comments = $validatedData['payment_mechanism_comments'] ?? null;

            // Q8 Apply in future
            $feedback->apply_in_future = $validatedData['apply_in_future'];
            $feedback->apply_in_future_comments = $validatedData['apply_in_future_comments'] ?? null;

            // Q9 Recommend service
            $feedback->recommend_service = $validatedData['recommend_service'];
            $feedback->recommend_service_comments = $validatedData['recommend_service_comments'] ?? null;

            // Q10 Advertising media (checkboxes as comma-separated)
            $feedback->advertising_media = $validatedData['advertising_media'];

            // Auto-populate service provider
            $feedback->service_provider_id = $application->service_provider_id;

            // No user_id for public submissions (nullable)
            $feedback->user_id = null;

            // Default values
            $feedback->wear_ppe = true; // Default assumption
            $feedback->price_reasonable = true; // Default

            $feedback->save();

            // Update application feedback status
            $application->feedback_status = true;
            $application->save();

            DB::commit();
            return $feedback;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
