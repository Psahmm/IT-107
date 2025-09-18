<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php"); // CONFIDENTIALITY: prevent unauthorized access
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user already has a reserved seat
$user_seat_check = $conn->prepare("SELECT * FROM seats WHERE user_id=?");
$user_seat_check->bind_param("i", $user_id);
$user_seat_check->execute();
$user_seat_result = $user_seat_check->get_result();
$user_reserved_seat = $user_seat_result->fetch_assoc(); // fetch single row if exists
$user_has_seat = $user_reserved_seat ? true : false;

// Handle seat selection if user doesn't have a seat yet
if(isset($_POST['seat']) && !$user_has_seat){
    $seat_id = $_POST['seat'];

    // INTEGRITY: check if seat is available
    $check = $conn->prepare("SELECT is_taken FROM seats WHERE id=?");
    $check->bind_param("i", $seat_id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if($result['is_taken'] == 0){
        $update = $conn->prepare("UPDATE seats SET is_taken=1, user_id=? WHERE id=?");
        $update->bind_param("ii", $user_id, $seat_id);
        $update->execute();
        $user_has_seat = true;

        // Refresh reserved seat info
        $user_reserved_seat = ['id'=>$seat_id];
    }
}

// Fetch all seats
$seats_result = $conn->query("SELECT * FROM seats ORDER BY seat_number ASC");

// Organize seats by rows
$rows = [];
while($seat = $seats_result->fetch_assoc()){
    $row_letter = substr($seat['seat_number'], 0, 1);
    $rows[$row_letter][] = $seat;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <p class="welcome">Welcome, <?= $_SESSION['username'] ?>!</p>

    <!-- CONFIDENTIALITY: only logged-in users see this -->
    <?php if($user_has_seat): ?>
        <p class="info">✅ Your reserved seat is highlighted in orange.</p>
    <?php else: ?>
        <p class="info">
            💺 Please pick your seat<br><br>
            <span>Available: <span style="background:#5cb85c; color:white; padding:2px 5px; border-radius:3px;">Green</span></span>
            <span style="margin-left:10px;">Taken: <span style="background:red; color:white; padding:2px 5px; border-radius:3px;">Red</span></span>
        </p>
    <?php endif; ?>

    <form method="POST" id="seatForm">
        <?php foreach($rows as $row_letter => $row_seats): ?>
            <div class="seat-row">
                <span class="row-label">Row <?= $row_letter ?>:</span>
                <div class="row-seats">
                    <?php foreach($row_seats as $seat): 
                        if($seat['user_id'] == $user_id){
                            $class = 'my-seat'; // ORANGE: user's seat (Confidentiality & Integrity)
                        } elseif($seat['is_taken']){
                            $class = 'taken'; // RED: taken (Integrity)
                        } else {
                            $class = 'available'; // GREEN: available
                        }
                    ?>
                        <button type="submit" name="seat" value="<?= $seat['id'] ?>"
                            class="seat-btn <?= $class ?>"
                            <?= ($seat['is_taken'] && $seat['user_id'] != $user_id) || $user_has_seat ? 'disabled' : '' ?> >
                            <?= $seat['seat_number'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </form>

    <div class="logout-container">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<script>
// CONFIDENTIALITY & Integrity: user can only select available seats and confirm choice
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.seat-btn').forEach(btn => {
        if(btn.classList.contains('available')){
            btn.onclick = function(e){
                const seatNumber = this.innerText;
                const confirmed = confirm("Do you want to reserve seat " + seatNumber + "?");
                if(!confirmed){
                    e.preventDefault();
                }
            };
        }
    });
});
</script>
</body>
</html>
