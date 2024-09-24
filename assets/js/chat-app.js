window.addEventListener("DOMContentLoaded", () => {
    //toggle chat
    const toggleChat = document.getElementById("chat-icon-toggle");
    const closeChat = document.getElementById("close-chat-btn");
    const formBlock = document.querySelector(".chat-form-block");
    const formContent = document.getElementById("chat-message-list");

    if (toggleChat) {
        toggleChat.addEventListener("click", () => {
            if (document.getElementById("chat-session-token-value")) {
                token = getSessionToken();
                document.getElementById("chat-session-token-value").value = token;
            }

            if (formBlock.classList.contains("active")) {
                if (formContent.classList.contains("active")) {
                    formContent.classList.remove("active");
                    formContent.classList.add("check");
                }
                formBlock.classList.remove("active");

                if (toggleChat.classList.contains("is-clicked")) {
                    toggleChat.classList.remove("is-clicked");
                } else {
                    toggleChat.classList.add("is-clicked");
                }
            } else {
                formContent.classList.remove("check");

                formBlock.classList.add("active");

                if (toggleChat.classList.contains("is-clicked")) {
                    toggleChat.classList.remove("is-clicked");
                } else {
                    toggleChat.classList.add("is-clicked");
                }
            }
        });
    }

    if (closeChat) {
        closeChat.addEventListener("click", () => {
            formBlock.classList.remove("active");
            toggleChat.classList.remove("is-clicked");
            if (formContent.classList.contains("active")) {
                formContent.classList.remove("active");
            }
        });
    }

    //custom select 
    const selectSingle = document.querySelector('.__select');
    const selectSingle_title = selectSingle.querySelector('.__select__title');
    const selectSingle_labels = selectSingle.querySelectorAll('.__select__label');

    // Toggle menu
    selectSingle_title.addEventListener('click', () => {
        if ('active' === selectSingle.getAttribute('data-state')) {
            selectSingle.setAttribute('data-state', '');
        } else {
            selectSingle.setAttribute('data-state', 'active');
        }
    });

    // Close when click to option
    for (let i = 0; i < selectSingle_labels.length; i++) {
        selectSingle_labels[i].addEventListener('click', (evt) => {
            selectSingle_title.textContent = evt.target.textContent;
            selectSingle.setAttribute('data-state', '');
        });
    }

    // Function to get session token 
    function getSessionToken() {
        const itemStr = localStorage.getItem("chat-session-token");

        // If the item doesn't exist, return null
        if (!itemStr) {
            return null;
        }

        document.getElementById("chat-session-token").value = itemStr;

        return itemStr;
    }

    // Set the session token if it doesn't exist or has expired
    const sessionToken = getSessionToken();
    if (!sessionToken) {
        const newSessionToken = generateToken(30);
        localStorage.setItem('chat-session-token', newSessionToken);
    }



    //send form data
    document.getElementById("chatForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission

        const user_id = document.getElementById("chat-hidden_user_id").value || null;
        const user_name = document.getElementById("chat-message-user-name").value || "User";
        const messageTopic = document.querySelector('.__select__title').innerText;
        //console.log(user_id);
        const messageText = document.getElementById("chat-message").value;
        const messageToken = generateToken(30); // Token generation function

        const sessionToken = document.getElementById("chat-session-token-value").value;

        const answer_to = document.getElementById("chat-answerTo").value || null;

        const data = {
            user_name: user_name,
            user_id: user_id,
            message_topic: messageTopic,
            message_text: messageText,
            message_token: messageToken,
            session_token: sessionToken,
            answer_to: answer_to
        };

        //console.log(data);

        // AJAX Request to submit the form
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "submit_message.php", true);
        xhr.setRequestHeader("Content-type", "application/json");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                console.log(xhr.responseText); // Response from server
                //alert("Message sent successfully!");

                //clear form
                document.getElementById("chat-message").value = '';
            }
        };

        xhr.send(JSON.stringify(data));
    });


    document.getElementById("chat-icon-toggle").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission
        //console.log(sessionToken);

        const sessionToken = document.getElementById("chat-session-token-value").value;

        const data = {
            session_token: sessionToken,
        };

        // AJAX Request to submit the form
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "get_new_messages.php", true);
        xhr.setRequestHeader("Content-type", "application/json");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {

                let chatList = document.getElementById("chat-message-list");

                if (chatList) {
                    chatList.innerHTML = xhr.responseText; // Ensure this is valid HTML from PHP
                }

            }
        };

        xhr.send(data);
    });


    // Generate a simple token for user identification
    function generateToken(tokenLength) {
        let result = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_*@#$%^&';
        const charactersLength = characters.length;
        let counter = 0;
        while (counter < tokenLength) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
            counter += 1;
        }
        return result;
    }


});



