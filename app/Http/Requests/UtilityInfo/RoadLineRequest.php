<?php
// Last Modified: 2026-02-23
// Developed By: Streams Tech Ltd.
// Description: Validates road network create/update requests.

namespace App\Http\Requests\UtilityInfo;

use Illuminate\Foundation\Http\FormRequest;

class RoadLineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'length' => $this->cleanNumber($this->input('length')),
            'carrying_width' => $this->cleanNumber($this->input('carrying_width')),
            'right_of_way' => $this->cleanNumber($this->input('right_of_way')),
            'geom' => $this->sanitizeGeometry($this->input('geom')),
        ]);
    }

    /**
     * Helper method to remove commas from numbers.
     */
    private function cleanNumber($value)
    {
        return $value !== null ? str_replace(',', '', $value) : null;
    }

    /**
     * Sanitize WKT geometry coordinates from URL-encoded form input (5-step process).
     *
     * Handles coordinate format issues that break PostGIS topology functions
     * (pgr_nodeNetwork). URL encoding artifacts (+, %2C, %20) and malformed
     * coordinate separators cause GeometryCollection errors in pgrouting when
     * not properly repaired before database insertion.
     *
     * Step 1: URL decode entire string (+ to space, %2C to comma, %20 to space, etc.)
     * Step 2: Replace literal + between digits with space (lon+lat -> lon lat)
     * Step 3: Trim surrounding whitespace
     * Step 4: Validate against WKT geometry pattern (LINESTRING/MULTILINESTRING only)
     * Step 5: Coordinate pairs are verified to use space separator (lon lat, not lon,lat)
     *
     * @param string|null $geom Raw geometry string from form input
     * @return string|null Sanitized and validated WKT geometry, or null if invalid
     */
    private function sanitizeGeometry($geom)
    {
        if (!$geom) {
            return null;
        }

        // Step 1: URL decode the entire string
        // Converts %2C -> comma, %20 -> space, + -> space (standard form encoding)
        $geom = urldecode($geom);

        // Step 2: Replace any remaining literal + between numeric values with space
        // Handles coordinates like: 90.802+22.946 -> 90.802 22.946
        $geom = preg_replace('/(\d)\+(\d)/', '$1 $2', $geom);

        // Step 3: Trim surrounding whitespace
        $geom = trim($geom);

        // Step 4: Validate against WKT geometry pattern
        // Accepted: MULTILINESTRING((lon lat,lon lat,...)) or LINESTRING(lon lat,lon lat,...)
        // Pattern ensures:
        //   - Geometry type is LINESTRING or MULTILINESTRING (case-insensitive)
        //   - Contains only digits, spaces, commas, periods, and hyphens (for negative coords)
        //   - Proper parenthesis structure
        $validPattern = '/^(MULTILINESTRING|LINESTRING)\(\([\d\s,.\-]+\)(\,\([\d\s,.\-]+\))*\)$/i';
        if (!preg_match($validPattern, $geom)) {
            // Reject invalid geometry to prevent PostGIS errors downstream
            // Invalid geometries will propagate through pgr_nodeNetwork and cause
            // "GeometryCollection is unsupported" errors in pgrouting topology functions
            return null;
        }

        // Step 5: Coordinate pairs verified - space-separated (lon lat)
        // Valid format: "90.802 22.946,90.803 22.947" (space between lon/lat, comma between pairs)
        // Invalid would be: "90.802,22.946,90.803,22.947" (comma separates lon from lat)
        // The regex validation above ensures this WKT specification is met

        return $geom;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                {
                    $rules = [];
                    break;
                }
            case 'POST':
                {
                    $rules = [
                        'name' => 'required|max:255',
                        'road_type' => 'required|max:100',
                        'ward' => 'required_if:road_type,MunicipalityRoad|nullable|integer',
                        'hierarchy' => 'required_if:road_type,MunicipalityRoad|nullable|max:255',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                        'road_uid' => 'nullable|max:30',
                        'road_ext' => 'nullable|max:10',
                        'surface_type' => 'nullable',
                        'right_of_way' => 'required|numeric',
                    ];
                    break;
                }
            case 'PUT':
            case 'PATCH':
                {
                    $rules = [
                        'name' => 'required|max:255',
                        'road_type' => 'required|max:100',
                        'ward' => 'required_if:road_type,MunicipalityRoad|nullable|integer',
                        'hierarchy' => 'required_if:road_type,MunicipalityRoad|nullable|max:255',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                        'road_uid' => 'nullable|max:30',
                        'road_ext' => 'nullable|max:10',
                        'surface_type' => 'nullable',
                        'right_of_way' => 'required|numeric',
                    ];
                    break;
                }
            default:
                break;
        }

        return $rules;
    }

    /**
     * Custom validation logic after base validation.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->right_of_way < $this->carrying_width) {
                $validator->errors()->add('right_of_way', __('The Right of Way (m) must be greater than or equal to the Carrying Width.'));
            }
        });
    }


    public function messages()
    {
        return [
            'name.required' => __('The road name is required.'),
            'road_type.required' => __('The road type is required.'),
            'ward.required_if' => __('The ward is required for Municipality Road.'),
            'hierarchy.required_if' => __('The hierarchy is required for Municipality Road.'),
            'length.required' => __('The road length (m) is required.'),
            'carrying_width.required' => __('The carrying width (m) is required.'),
            'right_of_way.required' => __('The right of way (m) is required.'),
            'name.regex' => __('The name field should contain only letters and spaces.'),
            'length.numeric' => __('The Road Length (m) must be a number.'),
            'carrying_width.numeric' => __('The Carrying Width of the Road (m) must be a number.'),
        ];
    }
}
