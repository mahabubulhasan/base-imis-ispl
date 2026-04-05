<?php
namespace App\Services\Fsm;

use App\Models\Fsm\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicApplicationService
{

    private function getValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'holding_owner_name' => 'nullable|string|max:255',
            'ward' => 'required|string',
            'road_code' => 'nullable|string|max:255',
            'tax_id' => [
                'required_if:has_tax_id,yes',
                'nullable',
                'string',
                'max:50',
            ],
            'address' => 'required|string|max:500',
            'proposed_emptying_date' => 'required|date|after_or_equal:today',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'customer_name.required' => 'Customer Name is required.',
            'customer_contact.required' => 'Contact No. is required.',
            'ward.required' => 'Ward is required.',
            'tax_id.required_if' => 'Tax ID is required when you indicate you have one.',
            'address.required' => 'Address is required.',
            'proposed_emptying_date.required' => 'Proposed Emptying Date is required.',
            'proposed_emptying_date.after_or_equal' => 'Proposed Emptying Date must be today or a future date.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ]);
    }

    public function createApplication(Request $request)
    {
        $validator = $this->getValidator($request);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Application::create([
                'tax_code' => $request->tax_id,
                'applicant_name' => $request->customer_name,
                'applicant_contact' => $request->customer_contact,
                'customer_name' => $request->holding_owner_name,
                'ward' => $request->ward,
                'road_code' => $request->road_code,
                'address' => $request->address,
                'proposed_emptying_date' => $request->proposed_emptying_date,
                'note' => $request->notes,
                'approved_status' => false,
                'application_date' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', "Error! Application couldn't be created.");
        }

        return redirect(route('client-fsm-application.form'))->with('success', 'Your application submitted successfully, Thank You.');
    }
}
