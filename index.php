<?php
$conn = new PDO("mysql:host=localhost:3306;dbname=ln_chat", "root", "");
$sql = "SELECT * FROM users";
$result = $conn->prepare($sql);
$result->execute([]);
$users = $result->fetchAll();
?>

<style>
    th,
    td {
        padding: 5px;
    }
</style>

<?php
session_start();

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $_SESSION['userId'] = $_POST['userId'];
}

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    echo "User ID: $userId";
}
?>
<form action="" method="post">
    <input type="text" name="userId">
    <input type="submit" value="Submit">
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['name']; ?></td>
                <td>
                    <?php if ($user['id'] != $userId) : ?>
                        <a href="./chat.php?id=<?= $user['id'] ?>">Chat</a>
                    <?php endif; ?>
                    <!-- <form method="POST" onsubmit="return sendEvent(this);">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>" required />
                        <input type="submit" value="Send Message" />
                    </form> -->
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- <ul id="messages"></ul> -->

<script src="socket.io.js"></script>

<script>
    // var userId = prompt("Enter user ID");

    console.log(<?= $userId ?>);
    // var userId = sessionStorage.getItem("userId");
    // if (!userId) {
    //     userId = prompt("Enter user ID");
    //     // Store userId in sessionStorage
    //     sessionStorage.setItem("userId", userId);
    // }

    // var socketIO = io("http://localhost:3000");
    // socketIO.emit("connected", userId);

    // socketIO.on("messageReceived", function(data) {
    //     var html = "<li>" + data + "</li>";
    //     document.getElementById("messages").innerHTML = html + document.getElementById("messages").innerHTML;
    // });

    // function sendEvent(form) {
    //     event.preventDefault();

    //     var message = prompt("Enter message");
    //     socketIO.emit("sendEvent", {
    //         "myId": userId,
    //         "userId": form.id.value,
    //         "message": message
    //     });
    // }
</script>