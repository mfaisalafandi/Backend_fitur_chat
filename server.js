var express = require("express");
var app = express();
 
var http = require("http").createServer(app);
var socketIO = require("socket.io")(http, {
    cors: {
        origin: "*"
    }
});

var mysql = require("mysql");
const { send } = require("process");
var connection = mysql.createConnection({
    host: "localhost",
    port: 3306,
    user: "root",
    password: "",
    database: "ln_chat"
});
 
connection.connect(function (error) {
    console.log("Database connected: " + error);
});
 
var users = [];
 
socketIO.on("connection", function (socket) {
 
    socket.on("connected", function (userId, yourId) {
        // users[userId] = socket.id;
        // var userPair = [userId, yourId].sort().join("");
        var userPair = [userId, yourId].join("");
        console.log(userPair);
        users[userPair] = socket.id;
        
        connection.query("SELECT * FROM messages WHERE (receiver_id = ? AND sender_id = ?) OR (receiver_id = ? AND sender_id = ?)", [userId, yourId, yourId, userId], function (error, messages) {
            if (error) {
                console.error("Error retrieving messages from database: ", error);
                return;
            }
            console.log(messages);

            // Kirim pesan ke klien
            messages.forEach(function (message) {
                var sender_id = message.sender_id;
                var receiver_id = message.receiver_id;
                var text = message.message;
                var createdDate = message.created_date;

                var createdDate = new Date(createdDate);

                var options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
                var createdDate = createdDate.toLocaleString('zh-CN', options).replace(/[/]/g, '-').replace(',', '');

                if (sender_id == userId) {
                    // Pesan yang dikirim oleh pengguna saat ini
                    connection.query("SELECT name FROM users WHERE id = " + receiver_id, function (error, sender) {
                        if (error) {
                            console.error("Error retrieving sender name from database: ", error);
                            return;
                        }

                        var senderName = sender.length > 0 ? sender[0].name : "Unknown";

                        var messageToSend = "<span class='sent'>" + text + "</span>" + "<br>" + "<span class='date-sent'>" + createdDate + "</span>";
                        socket.emit("messageReceived", messageToSend);
                    });
                } else {
                    // Pesan yang diterima oleh pengguna saat ini
                    connection.query("SELECT name FROM users WHERE id = " + sender_id, function (error, sender) {
                        if (error) {
                            console.error("Error retrieving sender name from database: ", error);
                            return;
                        }

                        var senderName = sender.length > 0 ? sender[0].name : "Unknown";

                        var messageToSend = "<span class='received'>" + text + "</span>" + "<br>" + "<span class='date-received'>" + createdDate + "</span>";
                        socket.emit("messageReceived", messageToSend);
                    });
                }
            });
        });
    });
 
    socket.on("sendEvent", async function (data) {

        var createdDate = new Date().toLocaleString('zh-CN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'Asia/Jakarta' 
        }).replace(/[/]/g, '-').replace(',', '');

        connection.query("SELECT * FROM users WHERE id = " + data.myId, function (error, sender) {
            if (sender.length > 0) {
                var senderName = sender[0].name;

                var messageToSend = "<span class='sent'>" + data.message + "</span>" + "<br>" + "<span class='date-sent'>" + createdDate + "</span>";
                socket.emit("messageReceived", messageToSend); // Mengirim pesan ke pengirim
            }
        });

        connection.query("SELECT * FROM users WHERE id = " + data.userId, function (error, receiver) {
            if (receiver != null) {
                if (receiver.length > 0) {
     
                    connection.query("SELECT * FROM users WHERE id = " + data.myId, function (error, sender) {
                        if (sender.length > 0) {
                            var message = "<span class='received'>" + data.message + "</span>" + "<br>" + "<span class='date-received'>" + createdDate + "</span>";
                            // socketIO.to(users[receiver[0].id]).emit("messageReceived", message);
                            
                            var userPair = [receiver[0].id, sender[0].id].join("");
                            socketIO.to(users[userPair]).emit("messageReceived", message);

                            var newMessage = {
                                sender_id: data.myId,
                                receiver_id: data.userId,
                                message: data.message,
                                created_date: createdDate, 
                            };

                            console.log(createdDate);
    
                            connection.query("INSERT INTO messages SET ?", newMessage, function (error, result) {
                                if (error) {
                                    console.error("Error inserting message into database: ", error);
                                    return;
                                }
                                console.log("Message inserted into database.");
                            });
                        }
                    });
                }
            }
        });
    });
});
 
http.listen(process.env.PORT || 3000, function () {
    console.log("Server is started.");
});