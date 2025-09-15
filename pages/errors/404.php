<?php

require('system/main.php');

$layout = new HTML(title: '404 Page Not Found');

?>

<div class="min-h-screen">
    <!-- 404 Error Section -->
    <section class="pt-10 pb-20 bg-gradient-to-br from-blue-50 via-white to-emerald-50 min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8 text-center lg:text-left">
                    <div class="space-y-6">
                        <div class="inline-block">
                            <span class="text-5xl font-bold bg-gradient-to-r from-blue-600 to-emerald-500 bg-clip-text text-transparent">
                                404
                            </span>
                        </div>
                        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight">
                            Page Not Found
                        </h1>
                        <p class="text-xl text-gray-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                            We can't seem to find the page you're looking for. Don't worry - our team is here to help you get back on the path to recovery.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="/" class="py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-md font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 shadow-lg flex items-center justify-center">
                            Return Home
                            <span class="ml-2 h-5 w-5">🏠</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include('partials/footer.php'); ?>
</div>