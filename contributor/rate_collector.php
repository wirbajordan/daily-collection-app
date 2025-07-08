<?php
include_once '../config/config.php';
$user_id = $_SESSION['user_id'];
$userName = '';
$stmt = $mysqli->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($userName);
$stmt->fetch();
$stmt->close();
$transactions = [];
$stmt = $mysqli->prepare("
    SELECT t.transaction_id, t.Date, t.amount, t.transaction_type, t.username as collector_username
    FROM transaction t 
    WHERE t.user_id = ? 
    ORDER BY t.Date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Debug information
error_log("User ID: " . $user_id);
error_log("Number of transactions found: " . count($transactions));
if (!empty($transactions)) {
    error_log("First transaction: " . print_r($transactions[0], true));
}

$ratings = [];
$res = $mysqli->query("SELECT transaction_id, rating, comment FROM collector_ratings WHERE contributor_id = $user_id");
while ($row = $res->fetch_assoc()) {
    $ratings[$row['transaction_id']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Collectors - DailyCollect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .star-rating-wrap {
            position: relative;
        }
        .star {
            font-size: 1.5rem;
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
            margin-right: 2px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            display: inline-block;
            padding: 2px;
        }
        .star.fa-star {
            color: #ffd700;
        }
        .star:hover,
        .star.hovered {
            color: #ffb700 !important;
            transform: scale(1.1);
        }
        .star:active {
            transform: scale(0.95);
        }
        .rating-text {
            font-weight: 500;
            color: #666;
        }
        .comment-form {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 8px;
        }
        .comment-feedback {
            font-size: 0.9rem;
        }
        .star-msg {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
            .star {
                font-size: 1.2rem !important;
            }
            .table td, .table th {
                padding: 0.5rem 0.3rem;
            }
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-star"></i> Rate Your Collectors</h1>
            <p class="mb-0">Share your experience with the collectors who helped you</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Your Transactions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Collector</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $starLabels = [1=>'Poor', 2=>'Fair', 3=>'Good', 4=>'Very Good', 5=>'Excellent'];
                                    
                                    if (empty($transactions)) {
                                        echo '<tr><td colspan="5" class="text-center text-muted py-4">';
                                        echo '<i class="fas fa-info-circle"></i> No transactions found. You need to have transactions with collectors to rate them.';
                                        echo '</td></tr>';
                                    } else {
                                        foreach ($transactions as $txn):
                                            $txn_id = $txn['transaction_id'];
                                            $collector_username = $txn['collector_username'];
                                            
                                            // Get collector ID from username
                                            $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ? AND role = 'collector'");
                                            $stmt->bind_param("s", $collector_username);
                                            $stmt->execute();
                                            $stmt->bind_result($collector_id);
                                            $stmt->fetch();
                                            $stmt->close();
                                            
                                            // Debug: Log collector info
                                            error_log("Collector username: " . $collector_username . ", Collector ID: " . ($collector_id ?? 'NULL'));
                                            
                                            // If collector_id is null, try to get it without role restriction
                                            if (!$collector_id) {
                                                $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
                                                $stmt->bind_param("s", $collector_username);
                                                $stmt->execute();
                                                $stmt->bind_result($collector_id);
                                                $stmt->fetch();
                                                $stmt->close();
                                                error_log("Retry - Collector ID: " . ($collector_id ?? 'NULL'));
                                            }
                                            
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($txn['Date']) . '</td>';
                                            echo '<td>' . htmlspecialchars($collector_username) . '</td>';
                                            echo '<td>' . htmlspecialchars($txn['amount']) . '</td>';
                                            echo '<td>' . htmlspecialchars($txn['transaction_type']) . '</td>';
                                            echo '<td>';
                                            
                                            $currentRating = isset($ratings[$txn_id]) ? (int)$ratings[$txn_id]['rating'] : 0;
                                            
                                            // Only show rating interface if we have a valid collector_id
                                            if ($collector_id) {
                                                echo '<div class="star-rating-wrap" data-txn="' . $txn_id . '" data-collector="' . $collector_id . '">';
                                                echo '<span class="star-rating">';
                                                
                                                for ($i = 1; $i <= 5; $i++) {
                                                    $starSymbol = ($i <= $currentRating) ? '★' : '☆';
                                                    $label = $starLabels[$i];
                                                    echo '<span class="star" data-value="' . $i . '" title="' . $label . '" style="font-size:1.5rem;cursor:pointer;color:' . ($i <= $currentRating ? '#ffd700' : '#ddd') . ';margin-right:2px;">' . $starSymbol . '</span>';
                                                }
                                                
                                                echo '</span>';
                                                echo '<span class="rating-text ms-2">';
                                                if ($currentRating) {
                                                    echo $currentRating . '/5 (' . ($currentRating * 20) . '%)';
                                                }
                                                echo '</span>';
                                                
                                                if ($currentRating && isset($ratings[$txn_id]['comment']) && $ratings[$txn_id]['comment']) {
                                                    echo '<br><small class="text-muted"><i class="fas fa-comment"></i> ' . htmlspecialchars($ratings[$txn_id]['comment']) . '</small>';
                                                }
                                                
                                                echo '<form class="comment-form" style="display:none;">
                                                    <input type="hidden" name="txn_id" value="' . $txn_id . '">
                                                    <input type="hidden" name="collector_id" value="' . $collector_id . '">
                                                    <input type="hidden" name="rating" value="">
                                                    <div class="mb-2">
                                                        <textarea class="form-control form-control-sm" name="comment" rows="2" maxlength="200" placeholder="Share your experience with this collector (optional)"></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Submit Rating
                                                    </button>
                                                    <span class="comment-feedback ms-2"></span>
                                                </form>';
                                                
                                                echo '<span class="star-msg text-success"></span>';
                                                echo '</div>';
                                            } else {
                                                echo '<span class="text-muted"><i class="fas fa-exclamation-triangle"></i> Collector not found</span>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                        endforeach;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, looking for star-rating-wrap elements...');
            const ratingWraps = document.querySelectorAll('.star-rating-wrap');
            console.log('Found ' + ratingWraps.length + ' rating wrap elements');
            
            ratingWraps.forEach(function(wrap, index) {
                const txnId = wrap.getAttribute('data-txn');
                const collectorId = wrap.getAttribute('data-collector');
                const stars = wrap.querySelectorAll('.star');
                const ratingText = wrap.querySelector('.rating-text');
                const commentForm = wrap.querySelector('.comment-form');
                const starMsg = wrap.querySelector('.star-msg');
                let selectedRating = 0;

                console.log('Setting up rating wrap ' + index + ' with ' + stars.length + ' stars');

                // Hover effects
                stars.forEach(function(star, idx) {
                    console.log('Adding event listeners to star ' + (idx + 1));
                    
                    star.addEventListener('mouseenter', function() {
                        console.log('Star ' + (idx + 1) + ' mouseenter');
                        stars.forEach((s, i) => {
                            if (i <= idx) {
                                s.classList.add('hovered');
                                s.textContent = '★';
                                s.style.color = '#ffb700';
                            } else {
                                s.classList.remove('hovered');
                                s.textContent = '☆';
                                s.style.color = '#ddd';
                            }
                        });
                        if (ratingText) {
                            ratingText.textContent = (idx + 1) + '/5 (' + ((idx + 1) * 20) + '%)';
                        }
                    });

                    star.addEventListener('mouseleave', function() {
                        console.log('Star ' + (idx + 1) + ' mouseleave');
                        stars.forEach((s, i) => {
                            s.classList.remove('hovered');
                            if (i < selectedRating) {
                                s.textContent = '★';
                                s.style.color = '#ffd700';
                            } else {
                                s.textContent = '☆';
                                s.style.color = '#ddd';
                            }
                        });
                        if (ratingText) {
                            ratingText.textContent = selectedRating ? selectedRating + '/5 (' + (selectedRating * 20) + '%)' : '';
                        }
                    });

                    star.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Star ' + (idx + 1) + ' clicked!');
                        selectedRating = parseInt(this.getAttribute('data-value'));
                        console.log('Selected rating: ' + selectedRating);
                        
                        stars.forEach((s, i) => {
                            if (i < selectedRating) {
                                s.textContent = '★';
                                s.style.color = '#ffd700';
                            } else {
                                s.textContent = '☆';
                                s.style.color = '#ddd';
                            }
                            s.classList.remove('hovered');
                        });
                        
                        if (ratingText) {
                            ratingText.textContent = selectedRating + '/5 (' + (selectedRating * 20) + '%)';
                        }

                        // Show comment form
                        if (commentForm) {
                            commentForm.style.display = 'block';
                            commentForm.querySelector('input[name=rating]').value = selectedRating;
                            commentForm.querySelector('textarea[name=comment]').focus();
                        }
                    });
                });

                // Comment form submit
                if (commentForm) {
                    commentForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        console.log('Form submitted');
                        
                        const rating = commentForm.querySelector('input[name=rating]').value;
                        const comment = commentForm.querySelector('textarea[name=comment]').value;
                        const feedback = commentForm.querySelector('.comment-feedback');
                        const submitBtn = commentForm.querySelector('button[type=submit]');
                        
                        // Disable submit button
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                        
                        fetch('save_collector_rating.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                transaction_id: txnId,
                                collector_id: collectorId,
                                rating: rating,
                                comment: comment
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                feedback.innerHTML = '<span style="color:green;"><i class="fas fa-check-circle"></i> Rating submitted successfully!</span>';
                                commentForm.style.display = 'none';
                                starMsg.innerHTML = '<i class="fas fa-check-circle"></i> Thank you for your feedback!';
                                
                                // Update the display to show the comment
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                feedback.innerHTML = '<span style="color:red;"><i class="fas fa-exclamation-circle"></i> ' + (data.error || 'Failed to save rating.') + '</span>';
                            }
                        })
                        .catch(error => {
                            feedback.innerHTML = '<span style="color:red;"><i class="fas fa-exclamation-circle"></i> Network error. Please try again.</span>';
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit Rating';
                        });
                    });
                }
            });
        });
    </script>
</body>
</html> 