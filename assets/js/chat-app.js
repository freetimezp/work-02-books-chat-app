window.addEventListener("DOMContentLoaded", () => {
    //toggle chat
    const toggleChat = document.getElementById("chat-icon-toggle");
    const closeChat = document.getElementById("close-chat-btn");
    const formBlock = document.querySelector(".chat-form-block");
    const formContent = document.getElementById("chat-message-list");

    if (toggleChat) {
        toggleChat.addEventListener("click", () => {
            if (toggleChat.classList.contains("is-clicked")) {
                toggleChat.classList.remove("is-clicked");
            } else {
                toggleChat.classList.add("is-clicked");
            }

            if (formBlock.classList.contains("active")) {
                // if (formContent.classList.contains("active")) {
                //     formContent.classList.remove("active");
                //     formContent.classList.add("check");
                // }
                formBlock.classList.remove("active");
            } else {
                //formContent.classList.remove("check");
                formBlock.classList.add("active");
            }
        });
    }

    if (closeChat) {
        closeChat.addEventListener("click", () => {
            formBlock.classList.remove("active");
            // if (formContent.classList.contains("active")) {
            //     formContent.classList.remove("active");
            // }
        });
    }

});