<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Number Categorization API",
 *     description="API for categorizing numbers based on their digit patterns. Numbers must be between 3 and 9 digits long.",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * @OA\Server(
 *     description="Local API server",
 *     url="http://localhost:8000/api"
 * )
 */
class NumberCategoryController extends Controller
{
    /**
     * @OA\Post(
     *     path="/categorize",
     *     summary="Categorize a number based on its digit patterns",
     *     description="Categorizes a number into Platinum, Gold, Silver, or Bronze based on the following rules:
     *     - Platinum: All digits are repeated (e.g., '111') or two repeated groups with equal length (e.g., '7771111')
     *     - Gold: Three or more pairs of repeated digits (e.g., '555222333')
     *     - Silver: At least one pair of repeated digits (e.g., '5543022')
     *     - Bronze: No repeated digits (e.g., '1234567')",
     *     tags={"Number Categorization"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number"},
     *             @OA\Property(
     *                 property="number",
     *                 type="string",
     *                 example="5543022",
     *                 description="The number to categorize. Must be between 3 and 9 digits long."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="category",
     *                 type="string",
     *                 example="Silver",
     *                 description="The category of the number (Platinum, Gold, Silver, or Bronze)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Number length must be between 3 and 9",
     *                 description="Error message describing the validation failure"
     *             )
     *         )
     *     )
     * )
     */
    public function categorize(Request $request): JsonResponse
    {
        // Validate input
        $request->validate([
            'number' => 'required|string|min:3|max:9'
        ]);

        $number = $request->input('number');
        $length = strlen($number);

        // Get the category
        $category = $this->determineCategory($number, $length);

        return response()->json([
            'category' => $category
        ]);
    }

    /**
     * Determine the category based on number patterns
     * 
     * Rules:
     * - Platinum: All digits are repeated or two repeated groups with equal length
     * - Gold: One group of 4 or more repeated digits
     * - Silver: One group of 3 repeated digits
     * - Bronze: No repeated digits
     */
    private function determineCategory(string $number, int $length): string
    {
        // Count occurrences of each digit
        $digitCounts = array_count_values(str_split($number));
        Log::info($digitCounts);
        
        // Check for all repeated digits (Platinum)
        if (count($digitCounts) === 1) {
            return 'Platinum';
        }

        // Check for two repeated groups with equal length (Platinum)
        // For example: 7771111 (3+4=7) or 5552222 (3+4=7)
        if (count($digitCounts) === 2) {
            $counts = array_values($digitCounts);
            // Check if the sum of the counts equals the length
            if ($counts[0] + $counts[1] === $length) {
                return 'Platinum';
            }
        }

        // Check for Gold pattern (one group of 4 or more repeated digits)
        foreach ($digitCounts as $count) {
            if ($count >= 4) {
                return 'Gold';
            }
        }

        // Check for Silver pattern (one group of 3 repeated digits)
        foreach ($digitCounts as $count) {
            if ($count >= 3) {
                return 'Silver';
            }
        }

        // Default to Bronze
        return 'Bronze';
    }
} 