<?php
session_start();

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $_SESSION['userId'] = $_POST['userId'];
}

if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    echo "User ID: $userId";
}

if (empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$friendId = $_GET['id'];
?>

<?php
$conn = new PDO("mysql:host=localhost:3306;dbname=ln_chat", "root", "");
$sql = "SELECT * FROM users WHERE id = '$friendId'";
$result = $conn->prepare($sql);
$result->execute([]);
$users = $result->fetchAll();
?>

<style>
    /* .sent {
        text-align: right;
        color: blue;
    }

    .received {
        text-align: left;
        color: green;
    } */

    #messages {
        /* width: 50px; */
        margin-bottom: 10px;
        clear: both;
        /* max-width: 50% !important;
        width: auto !important; */
    }

    .sent,
    .date-sent {
        float: right;
    }

    .received {
        float: left;
    }

    .sent,
    .received {
        background-color: #b2d8ff;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
        clear: both;
        width: auto;
        box-sizing: border-box;
    }

    .date-received,
    .date-sent {
        background-color: transparent;
        font-size: 10px;
    }
</style>

<br><br>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['name']; ?></td>
                <!-- <td>
                    <form method="POST" onsubmit="return sendEvent(this);">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>" required />
                        <input type="submit" value="Send Message" />
                    </form>
                </td> -->
            </tr>
            <tr>
                <td colspan="2">
                    <form method="POST" onsubmit="return sendEvent(this);">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>" required />
                        <input type="text" name="message" value="" required />
                        <input type="submit" value="Send Message" />
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<a href="./index.php">Back</a>

<br><br>

<!-- <ul id="messages"></ul> -->
<div id="messages"></div>

<script src="socket.io.js"></script>

<script>
    // var userId = prompt("Enter user ID");
    var userId = <?= $userId ?>;
    var yourId = <?= $_GET['id'] ?>;

    var socketIO = io("http://localhost:3000");
    socketIO.emit("connected", userId, yourId);

    socketIO.on("messageReceived", function(data) {
        // var html = "<li>" + data + "</li>";
        var html = data + "<br><br>";
        document.getElementById("messages").innerHTML = html + document.getElementById("messages").innerHTML;
    });

    // socketIO.on("messageReceived", function(data) {
    //     var messageContainer = document.createElement("div");
    //     var className = sender_id == userId ? "sent" : "received";
    //     messageContainer.classList.add("message");
    //     messageContainer.innerHTML = "<span class='" + className + "'>" + data + "</span>";
    //     document.getElementById("messages").appendChild(messageContainer);
    // });

    function sendEvent(form) {
        event.preventDefault();

        // var message = prompt("Enter message");
        socketIO.emit("sendEvent", {
            "myId": userId,
            "userId": form.id.value,
            "message": form.message.value
            // "message": message
        });

        form.message.value = "";
    }
</script>