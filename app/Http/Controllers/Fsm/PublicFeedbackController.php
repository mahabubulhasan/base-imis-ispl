<?php
// Last Modified Date: 09-02-2026
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\PublicFeedbackRequest;
use App\Services\Fsm\PublicFeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicFeedbackController extends Controller
{
    private $_feedbackService;

    public function __construct(PublicFeedbackService $feedbackService)
    {
        $this->_feedbackService = $feedbackService;
    }

    /**
     * Get application data for AJAX lookup
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApplicationData(Request $request)
    {
        try {
            $applicationId = $request->input('application_id');

            if (!$applicationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application Number is required.'
                ], 400);
            }

            $applicationData = $this->_feedbackService->getApplicationDetails($applicationId);

            if (!$applicationData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found. Please check the Application Number.'
                ], 404);
            }

            // Check for error in response (e.g., feedback already exists)
            if (isset($applicationData['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $applicationData['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $applicationData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching application data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching application data.'
            ], 500);
        }
    }

    /**
     * Submit public feedback form
     *
     * @param PublicFeedbackRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitFeedback(PublicFeedbackRequest $request)
    {
        try {
            $ipAddress = $request->ip();

            // Anti-spam check: Honeypot (checked in Request validation)

            // Anti-spam check: Time-based validation
            $formLoadedAt = $request->input('form_loaded_at');
            $currentTime = time();
            $timeDifference = $currentTime - $formLoadedAt;

            if ($timeDifference < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form submitted too quickly. Please take your time to fill out the feedback.'
                ], 400);
            }

            // Anti-spam check: Rate limiting (IP-based)
            if ($this->_feedbackService->checkRateLimit($ipAddress)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have submitted too many feedbacks. Please try again later.'
                ], 429);
            }

            // Anti-spam check: Duplicate submission
            if ($this->_feedbackService->checkDuplicateSubmission($request->application_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feedback for this application has already been submitted.'
                ], 400);
            }

            // Store feedback
            $feedback = $this->_feedbackService->storeFeedback($request->validated());

            // Increment rate limit counter
            $this->_feedbackService->incrementRateLimit($ipAddress);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback! Your response has been submitted successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error submitting feedback: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting your feedback. Please try again.'
            ], 500);
        }
    }
}
