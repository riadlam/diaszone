@extends('layouts.app')

@section('title', __('home.game_' . $gameType) . ' - DiasZone')

@section('content')
<!-- Game Header -->
@include('components.game-header', ['gameType' => $gameType, 'gameTitle' => $gameTitle, 'gameImage' => $gameImage ?? null, 'averageRating' => $averageRating ?? 5.0, 'totalReviews' => $totalReviews ?? 0])

<!-- Main Content: Offers and Order Form -->
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen">
    <div class="container mx-auto px-4" style="padding-top: 1rem !important; padding-bottom: 2rem !important;" id="main-container">
        <div id="offers-section" class="flex flex-col lg:flex-row">
            <!-- Left Column: Diamond Packs (Scrollable) - Hidden on mobile -->
            <div class="flex-1 hidden lg:block" style="margin-right: 15px !important;">
                @include('components.diamond-packs', ['packs' => $packs, 'gameType' => $gameType, 'gameTitle' => $gameTitle, 'gameImage' => $gameImage ?? null])
            </div>
            
            <!-- Right Column: Order Form (Sticky on desktop, full width on mobile) -->
            <div id="order-form-wrapper" class="w-full lg:w-96 lg:mt-0" data-game-type="{{ $gameType }}">
                <!-- Mobile: Select Pack Button (moved here to be in same column) -->
                <div class="lg:hidden mb-4" id="mobile-select-pack-container">
                    <button id="mobile-select-pack-btn" 
                            type="button"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors shadow-md hover:shadow-lg flex items-center justify-between">
                        <span id="mobile-selected-pack-text" class="text-left">
                            <span class="block text-sm font-medium">{{ __('home.select_topup_amount') }}</span>
                            <span id="mobile-selected-pack-details" class="text-xs opacity-75 hidden"></span>
                        </span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                
                @include('components.order-form', ['gameTitle' => $gameTitle, 'gameType' => $gameType, 'game' => $game ?? null])
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Sheet - Always accessible, outside hidden column -->
@include('components.mobile-bottom-sheet', ['packs' => $packs, 'gameType' => $gameType, 'gameTitle' => $gameTitle, 'gameImage' => $gameImage ?? null])

<!-- Recharge Info Section -->
@include('components.recharge-info', ['gameType' => $gameType, 'gameTitle' => $gameTitle, 'game' => $game ?? null, 'averageRating' => $averageRating ?? 0, 'totalReviews' => $totalReviews ?? 0, 'reviews' => $reviews ?? collect([])])

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gameId = @json(isset($game) && $game ? $game->id : null);
    if (!gameId) return;

    // Elements
    const reviewForm = document.getElementById('review-form');
    const reviewFormContainer = document.getElementById('review-form-container');
    const leaveReviewBtn = document.getElementById('leave-review-btn');
    const submitBtn = document.getElementById('review-submit-btn');
    const submitText = document.getElementById('review-submit-text');
    const reviewMessage = document.getElementById('review-message');
    const reviewName = document.getElementById('review-name');
    const reviewComment = document.getElementById('review-comment');
    const reviewRatingInput = document.getElementById('review-rating');
    const ratingStars = document.querySelectorAll('.rating-star');
    const reviewListContainer = document.getElementById('review-list-container');
    const overallRatingContainer = document.querySelector('.bg-purple-50.border.border-purple-200');

    // Toggle review form
    if (leaveReviewBtn && reviewFormContainer) {
        leaveReviewBtn.addEventListener('click', function() {
            reviewFormContainer.style.display = reviewFormContainer.style.display === 'none' ? 'block' : 'none';
        });
    }

    // Star rating interaction
    if (ratingStars && reviewRatingInput) {
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                reviewRatingInput.value = rating;
                updateStarDisplay(rating);
            });
        });
    }

    function updateStarDisplay(rating) {
        ratingStars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    // Load reviews and rating via AJAX
    async function loadReviews() {
        try {
            const response = await fetch(`/api/games/${gameId}/reviews`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                console.error('Failed to load reviews');
                return;
            }

            const data = await response.json();
            if (data.success) {
                updateRatingDisplay(data.averageRating, data.totalReviews);
                updateReviewsList(data.reviews);
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
        }
    }

    // Update rating display
    function updateRatingDisplay(averageRating, totalReviews) {
        if (!overallRatingContainer) return;

        const ratingValue = averageRating || 5.0;
        const roundedRating = Math.round(ratingValue);

        // Update rating number
        const ratingSpan = overallRatingContainer.querySelector('.text-3xl.font-bold.text-purple-600');
        if (ratingSpan) {
            ratingSpan.textContent = ratingValue.toFixed(1);
        }

        // Update stars
        const starContainer = overallRatingContainer.querySelector('.flex.text-yellow-400');
        if (starContainer) {
            const stars = starContainer.querySelectorAll('svg');
            stars.forEach((star, index) => {
                if (index < roundedRating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            });
        }

        // Update review count
        const reviewCountSpan = overallRatingContainer.querySelector('.text-sm.text-gray-600');
        if (reviewCountSpan) {
            if (totalReviews > 0) {
                reviewCountSpan.textContent = `Based on ${totalReviews} ${totalReviews === 1 ? 'review' : 'reviews'}`;
            } else {
                reviewCountSpan.textContent = 'No reviews yet';
            }
        }
    }

    // Update reviews list
    function updateReviewsList(reviews) {
        if (!reviewListContainer) return;

        if (reviews && reviews.length > 0) {
            reviewListContainer.innerHTML = reviews.map(review => {
                const likesCount = review.likesCount || 0;
                const dislikesCount = review.dislikesCount || 0;
                const repliesCount = review.repliesCount || 0;
                const userLike = review.userLike || null;
                
                const likeBtnClass = userLike === 'like' ? 'text-green-600' : 'text-gray-600 hover:text-green-600';
                const dislikeBtnClass = userLike === 'dislike' ? 'text-red-600' : 'text-gray-600 hover:text-red-600';
                
                return `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 review-item" data-review-id="${review.id}">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex">
                            ${Array.from({length: 5}, (_, i) => `
                                <svg class="w-4 h-4 ${i < review.rating ? 'text-yellow-400' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            `).join('')}
                        </div>
                        <span class="text-sm font-semibold text-gray-900">${escapeHtml(review.name)}</span>
                        <span class="text-xs text-gray-500">${review.created_at}</span>
                    </div>
                    <p class="text-sm text-gray-700 mb-3">${escapeHtml(review.comment)}</p>
                    
                    <!-- Like/Dislike Buttons -->
                    <div class="flex items-center gap-4 mb-3">
                        <button type="button" class="review-like-btn flex items-center gap-1 text-sm ${likeBtnClass} transition-colors" data-review-id="${review.id}" data-type="like">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                            </svg>
                            <span class="review-likes-count">${likesCount}</span>
                        </button>
                        <button type="button" class="review-dislike-btn flex items-center gap-1 text-sm ${dislikeBtnClass} transition-colors" data-review-id="${review.id}" data-type="dislike">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
                            </svg>
                            <span class="review-dislikes-count">${dislikesCount}</span>
                        </button>
                        <button type="button" class="review-reply-toggle-btn flex items-center gap-1 text-sm text-gray-600 hover:text-purple-600 transition-colors" data-review-id="${review.id}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="review-replies-count">${repliesCount}</span>
                        </button>
                    </div>
                    
                    <!-- Replies Section -->
                    <div class="review-replies-container hidden mt-3 border-t border-gray-200 pt-3" data-review-id="${review.id}">
                        <div class="review-replies-list mb-3 space-y-2"></div>
                        
                        <!-- Reply Form -->
                        <form class="review-reply-form" data-review-id="${review.id}">
                            <div class="mb-2">
                                <input type="text" 
                                       class="review-reply-name w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" 
                                       placeholder="Your name" 
                                       required 
                                       maxlength="100">
                            </div>
                            <div class="mb-2">
                                <textarea class="review-reply-comment w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm resize-none" 
                                          rows="2" 
                                          placeholder="Write a reply..." 
                                          required 
                                          minlength="3" 
                                          maxlength="500"></textarea>
                            </div>
                            <button type="submit" class="review-reply-submit-btn px-4 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                Reply
                            </button>
                        </form>
                    </div>
                </div>
            `;
            }).join('');
            reviewListContainer.style.display = 'block';
            
            // Attach event listeners after rendering
            attachReviewEventListeners();
        } else {
            reviewListContainer.style.display = 'none';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Handle form submission
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!reviewRatingInput || parseInt(reviewRatingInput.value) < 1) {
                showMessage('Please select a rating.', 'error');
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                if (submitText) submitText.textContent = 'Submitting...';
            }

            if (reviewMessage) {
                reviewMessage.classList.add('hidden');
            }

            const formData = new FormData(reviewForm);
            
            // Ensure game_id is included (in case hidden field is missing)
            if (gameId && !formData.has('game_id')) {
                formData.append('game_id', gameId);
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const response = await fetch('{{ route("api.games.review") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showMessage(data.message || 'Review submitted successfully!', 'success');
                    
                    // Reset form
                    reviewForm.reset();
                    reviewRatingInput.value = '0';
                    updateStarDisplay(0);
                    
                    // Update reviews and rating immediately from response data (no extra API call needed)
                    if (data.averageRating !== undefined && data.totalReviews !== undefined) {
                        updateRatingDisplay(data.averageRating, data.totalReviews);
                    }
                    if (data.reviews && Array.isArray(data.reviews)) {
                        updateReviewsList(data.reviews);
                    } else {
                        // Fallback: reload if response doesn't include updated reviews
                        await loadReviews();
                    }
                    
                    // Hide form after 2 seconds
                    setTimeout(() => {
                        if (reviewFormContainer) {
                            reviewFormContainer.style.display = 'none';
                        }
                    }, 2000);
                } else {
                    showMessage(data.message || 'Failed to submit review. Please try again.', 'error');
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitText) submitText.textContent = 'Submit Review';
                }
            } catch (error) {
                console.error('Review submission error:', error);
                showMessage('An error occurred. Please try again.', 'error');
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.textContent = 'Submit Review';
            }
        });
    }

    function showMessage(message, type) {
        if (!reviewMessage) return;
        
        reviewMessage.textContent = message;
        reviewMessage.className = 'mt-4 p-4 rounded-lg';
        
        if (type === 'success') {
            reviewMessage.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-400');
        } else {
            reviewMessage.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-400');
        }
        
        reviewMessage.classList.remove('hidden');
    }

    // Attach event listeners for likes/dislikes and replies
    function attachReviewEventListeners() {
        // Like/Dislike buttons
        document.querySelectorAll('.review-like-btn, .review-dislike-btn').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const reviewId = this.getAttribute('data-review-id');
                const type = this.getAttribute('data-type');
                
                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                try {
                    const response = await fetch(`/api/reviews/${reviewId}/like`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ type: type })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        // Update counts
                        const reviewItem = btn.closest('.review-item');
                        const likesCountEl = reviewItem.querySelector('.review-likes-count');
                        const dislikesCountEl = reviewItem.querySelector('.review-dislikes-count');
                        const likeBtn = reviewItem.querySelector('.review-like-btn');
                        const dislikeBtn = reviewItem.querySelector('.review-dislike-btn');
                        
                        if (likesCountEl) likesCountEl.textContent = data.likesCount || 0;
                        if (dislikesCountEl) dislikesCountEl.textContent = data.dislikesCount || 0;
                        
                        // Update button styles based on user's current like status
                        if (data.userLike === 'like') {
                            likeBtn.classList.remove('text-gray-600', 'hover:text-green-600');
                            likeBtn.classList.add('text-green-600');
                            dislikeBtn.classList.remove('text-red-600');
                            dislikeBtn.classList.add('text-gray-600', 'hover:text-red-600');
                        } else if (data.userLike === 'dislike') {
                            dislikeBtn.classList.remove('text-gray-600', 'hover:text-red-600');
                            dislikeBtn.classList.add('text-red-600');
                            likeBtn.classList.remove('text-green-600');
                            likeBtn.classList.add('text-gray-600', 'hover:text-green-600');
                        } else {
                            // No like/dislike
                            likeBtn.classList.remove('text-green-600');
                            likeBtn.classList.add('text-gray-600', 'hover:text-green-600');
                            dislikeBtn.classList.remove('text-red-600');
                            dislikeBtn.classList.add('text-gray-600', 'hover:text-red-600');
                        }
                    } else {
                        showMessage(data.message || 'An error occurred.', 'error');
                    }
                } catch (error) {
                    console.error('Error toggling like:', error);
                    showMessage('An error occurred. Please try again.', 'error');
                } finally {
                    btn.disabled = false;
                }
            });
        });
        
        // Reply toggle buttons
        document.querySelectorAll('.review-reply-toggle-btn').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const reviewId = this.getAttribute('data-review-id');
                const repliesContainer = document.querySelector(`.review-replies-container[data-review-id="${reviewId}"]`);
                
                if (!repliesContainer) return;
                
                if (repliesContainer.classList.contains('hidden')) {
                    repliesContainer.classList.remove('hidden');
                    await loadReplies(reviewId);
                } else {
                    repliesContainer.classList.add('hidden');
                }
            });
        });
        
        // Reply form submissions
        document.querySelectorAll('.review-reply-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const reviewId = this.getAttribute('data-review-id');
                const nameInput = this.querySelector('.review-reply-name');
                const commentInput = this.querySelector('.review-reply-comment');
                const submitBtn = this.querySelector('.review-reply-submit-btn');
                
                if (!nameInput || !commentInput) return;
                
                const name = nameInput.value.trim();
                const comment = commentInput.value.trim();
                
                if (!name || !comment) {
                    showMessage('Please fill in all fields.', 'error');
                    return;
                }
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Submitting...';
                }
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                try {
                    const response = await fetch(`/api/reviews/${reviewId}/reply`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            name: name,
                            comment: comment
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        showMessage(data.message || 'Reply submitted successfully!', 'success');
                        
                        // Reset form
                        nameInput.value = '';
                        commentInput.value = '';
                        
                        // Reload replies
                        await loadReplies(reviewId);
                        
                        // Update replies count
                        const repliesCountEl = document.querySelector(`.review-reply-toggle-btn[data-review-id="${reviewId}"] .review-replies-count`);
                        if (repliesCountEl) {
                            const repliesList = document.querySelector(`.review-replies-list[data-review-id="${reviewId}"]`) || 
                                               document.querySelector(`.review-replies-container[data-review-id="${reviewId}"] .review-replies-list`);
                            if (repliesList) {
                                const count = repliesList.querySelectorAll('.review-reply-item').length + 1;
                                repliesCountEl.textContent = count;
                            }
                        }
                    } else {
                        showMessage(data.message || 'An error occurred.', 'error');
                    }
                } catch (error) {
                    console.error('Error submitting reply:', error);
                    showMessage('An error occurred. Please try again.', 'error');
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Reply';
                    }
                }
            });
        });
    }
    
    // Load replies for a review
    async function loadReplies(reviewId) {
        try {
            const response = await fetch(`/api/reviews/${reviewId}/replies`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                console.error('Failed to load replies');
                return;
            }
            
            const data = await response.json();
            if (data.success && data.replies) {
                const repliesList = document.querySelector(`.review-replies-container[data-review-id="${reviewId}"] .review-replies-list`);
                if (repliesList) {
                    if (data.replies.length > 0) {
                        repliesList.innerHTML = data.replies.map(reply => `
                            <div class="review-reply-item bg-white border border-gray-200 rounded-lg p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-semibold text-gray-900">${escapeHtml(reply.name)}</span>
                                    <span class="text-xs text-gray-500">${reply.created_at}</span>
                                </div>
                                <p class="text-sm text-gray-700">${escapeHtml(reply.comment)}</p>
                            </div>
                        `).join('');
                    } else {
                        repliesList.innerHTML = '<p class="text-sm text-gray-500">No replies yet.</p>';
                    }
                }
            }
        } catch (error) {
            console.error('Error loading replies:', error);
        }
    }

    // Load reviews on page load
    loadReviews();
    
    // Attach listeners on initial page load (for server-rendered reviews)
    attachReviewEventListeners();
});
</script>
@endpush
@endsection

