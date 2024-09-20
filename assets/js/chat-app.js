window.addEventListener("DOMContentLoaded", () => {
    const toggleChatAppBtn = document.querySelector(".chat-icon");

    if (toggleChatAppBtn) {
        toggleChatAppBtn.addEventListener("click", () => {
            if (toggleChatAppBtn.classList.contains("is-clicked")) {
                toggleChatAppBtn.classList.remove("is-clicked");
            } else {
                toggleChatAppBtn.classList.add("is-clicked");
            }
        });
    }

});