window.addEventListener("DOMContentLoaded", () => {
    //toggle chat
    const toggleChat = document.getElementById("chat-icon-toggle");
    const closeChat = document.getElementById("close-chat-btn");
    const formBlock = document.querySelector(".chat-form-block");
    const formContent = document.getElementById("chat-message-list");

    if (toggleChat) {
        toggleChat.addEventListener("click", () => {
            if (formBlock.classList.contains("active")) {
                // if (formContent.classList.contains("active")) {
                //     formContent.classList.remove("active");
                //     formContent.classList.add("check");
                // }
                formBlock.classList.remove("active");

                if (toggleChat.classList.contains("is-clicked")) {
                    toggleChat.classList.remove("is-clicked");
                } else {
                    toggleChat.classList.add("is-clicked");
                }
            } else {
                //formContent.classList.remove("check");
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
            // if (formContent.classList.contains("active")) {
            //     formContent.classList.remove("active");
            // }
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
});