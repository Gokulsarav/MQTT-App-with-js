<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MQTT Dynamic Chat</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mqtt/4.3.7/mqtt.min.js"></script>
    <style>
        body {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }
        .section {
            width: 45%;
        }
        #messages, #subscribedMessages {
            border: 1px solid #000;
            padding: 10px;
            height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="section">
        <h2>Publisher</h2>
        <label for="publishTopic">Enter topic:</label>
        <input type="text" id="publishTopic" placeholder="Topic">
        <br><br>
        <label for="pubMessage">Enter message:</label>
        <input type="text" id="pubMessage" placeholder="Message">
        <button onclick="publishMessage()">Send</button>
    </div>
    
    <div class="section">
        <h2>Subscriber</h2>
        <label for="subscribeTopic">Subscribe to topic:</label>
        <input type="text" id="subscribeTopic" placeholder="Topic">
        <button onclick="subscribeToTopic()">Subscribe</button>
        <h3>Received Messages:</h3>
        <div id="subscribedMessages"></div>
        <br>
        <label for="subMessage">Send message:</label>
        <input type="text" id="subMessage" placeholder="Message">
        <button onclick="sendSubscriberMessage()">Send</button>
    </div>
    
    <script>
        const client = mqtt.connect('ws://test.mosquitto.org:8080');
        let subscribedTopic = "";

        client.on("connect", function () {
            console.log("Connected to MQTT Broker (Mosquitto)");
        });

        client.on("message", function (topic, message) {
            if (topic === subscribedTopic) {
                const msgObj = JSON.parse(message.toString());
                const chatBox = document.getElementById("subscribedMessages");
                chatBox.innerHTML += `<p><strong>${msgObj.user}:</strong> ${msgObj.text}</p>`;
            }
        });

        function publishMessage() {
            const topic = document.getElementById("publishTopic").value.trim();
            const msg = document.getElementById("pubMessage").value.trim();

            if (topic === "" || msg === "") {
                alert("Please enter a topic and a message.");
                return;
            }

            const messageObj = { user: "Publisher", text: msg };
            client.publish(topic, JSON.stringify(messageObj));
            document.getElementById("pubMessage").value = "";
        }

        function subscribeToTopic() {
            subscribedTopic = document.getElementById("subscribeTopic").value.trim();
            if (subscribedTopic === "") {
                alert("Please enter a topic to subscribe to.");
                return;
            }
            client.subscribe(subscribedTopic, function (err) {
                if (!err) {
                    console.log("Subscribed to topic:", subscribedTopic);
                }
            });
        }

        function sendSubscriberMessage() {
            const msg = document.getElementById("subMessage").value.trim();
            if (subscribedTopic === "") {
                alert("Please subscribe to a topic first.");
                return;
            }
            if (msg === "") {
                alert("Please enter a message.");
                return;
            }
            const messageObj = { user: "Subscriber", text: msg };
            client.publish(subscribedTopic, JSON.stringify(messageObj));
            document.getElementById("subMessage").value = "";
        }
    </script>
</body>
</html>