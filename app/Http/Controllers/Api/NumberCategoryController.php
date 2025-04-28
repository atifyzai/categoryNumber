<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Number Categorization API",
 *     description="API for categorizing numbers based on length and digit patterns",
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
     *     summary="Categorize a number based on its length and digit patterns",
     *     tags={"Number Categorization"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"number", "length"},
     *             @OA\Property(property="number", type="string", example="5543022"),
     *             @OA\Property(property="length", type="integer", example=7)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="category", type="string", example="Gold")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Length must be between 3 and 9")
     *         )
     *     )
     * )
     */
    public function categorize(Request $request): JsonResponse
    {
        // Validate input
        $request->validate([
            'number' => 'required|string',
            'length' => 'required|integer|min:3|max:9'
        ]);

        $number = $request->input('number');
        $length = $request->input('length');

        // Check if number length matches the provided length
        if (strlen($number) !== $length) {
            return response()->json([
                'error' => "Number length must be equal to the provided length"
            ], 400);
        }

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
     * - Gold: Two pairs of repeated digits
     * - Silver: At least one pair of repeated digits
     * - Bronze: No repeated digits
     */
    private function determineCategory(string $number, int $length): string
    {
        // Count occurrences of each digit
        $digitCounts = array_count_values(str_split($number));
        
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

        // Check for Gold pattern (two pairs of repeated digits)
        $pairs = 0;
        foreach ($digitCounts as $count) {
            if ($count >= 2) {
                $pairs++;
            }
        }
        if ($pairs >= 2) {
            return 'Gold';
        }

        // Check for Silver pattern (at least one pair)
        if ($pairs >= 1) {
            return 'Silver';
        }

        // Default to Bronze
        return 'Bronze';
    }
} 