<?php

header('Content-Type: text/plain');

use App\Lib\Wextractor;

// Example usage of the Wextractor class
try {
    // Initialize the Wextractor client with your API token
    $wextractor = new Wextractor(WEXTRACTOR_API_KEY, 'ChIJl2VcM13lXAQR7SyMQ7pprh0');

    echo "Fetching all reviews for place ID: {$wextractor->placeId}\n";
    echo "==============================================\n\n";

    // Fetch all reviews (this will automatically handle pagination)
    $allReviews = $wextractor->fetchAllReviews();

    // Display place details
    if ($allReviews['place_details']) {
        echo "Place: " . $allReviews['place_details']['name'] . "\n";
        echo "Address: " . $allReviews['place_details']['address'] . "\n\n";
    }

    // Display totals
    if ($allReviews['totals']) {
        echo "Total Reviews: " . $allReviews['totals']['review_count'] . "\n";
        echo "Average Rating: " . $allReviews['totals']['average_rating'] . "\n";
        echo "Reviews Fetched: " . $allReviews['total_fetched'] . "\n\n";
    }

    // Display first few reviews as examples
    echo "Sample Reviews:\n";
    echo "===============\n";

    $sampleReviews = array_slice($allReviews['reviews'], 0, 3);
    foreach ($sampleReviews as $index => $review) {
        echo ($index + 1) . ". " . $review['reviewer'] . " - Rating: " . $review['rating'] . "/5\n";
        echo "   Date: " . $review['datetime'] . "\n";
        echo "   Review: " . ($review['text'] ?: '[No text]') . "\n";
        echo "   Language: " . ($review['language'] ?: 'Unknown') . "\n";
        echo "   Likes: " . $review['likes'] . "\n\n";
    }


    echo "----------------JSON----------------\n";

    $jsonReviews = json_encode($allReviews, JSON_PRETTY_PRINT);
    echo $jsonReviews;

    echo "----------------JSON----------------\n";

    // Example of using other methods
    echo "Additional Examples:\n";
    echo "===================\n";

    // Get reviews sorted by highest rating
    $highestRatedReviews = $wextractor->getReviewsBySort('highest_rating');
    echo "- Highest rated reviews: " . count($highestRatedReviews['reviews']) . " fetched\n";

    // Get reviews in Spanish
    $spanishReviews = $wextractor->getReviewsByLanguage('es');
    echo "- Spanish reviews: " . count($spanishReviews['reviews']) . " fetched\n";

    // Get reviews with keyword statistics
    $reviewsWithKeywords = $wextractor->getReviewsWithKeywords();
    echo "- Reviews with keywords: " . count($reviewsWithKeywords['reviews']) . " fetched\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nUsage Examples:\n";
echo "===============\n";
echo "1. Fetch all reviews: \$wextractor->fetchAllReviews()\n";
echo "2. Sort by rating: \$wextractor->getReviewsBySort('highest_rating')\n";
echo "3. Language filter: \$wextractor->getReviewsByLanguage('es')\n";
echo "4. With keywords: \$wextractor->getReviewsWithKeywords()\n";
echo "5. Single page: \$wextractor->getReviewsPage(0)\n";
