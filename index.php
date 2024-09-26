<?php
session_start(); // starts the session
//save main page in session
$_SESSION['url'] = $_SERVER['REQUEST_URI'];
//print_r($_SESSION);

//connect to database SQL
require("connectDB.php");

//functions
require("functions.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <!--=============== FAVICON ===============-->
   <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">

   <!--=============== REMIXICONS ===============-->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css">

   <!--=============== SWIPER CSS ===============-->
   <link rel="stylesheet" href="./assets/css/swiper-bundle.min.css">

   <!--=============== main CSS ===============-->
   <link rel="stylesheet" href="assets/css/styles.css">

   <!--=============== chat app CSS ===============-->
   <link rel="stylesheet" href="assets/css/chat-app.scss">

   <title>Books</title>

   <!--=============== ajax ===============-->
   <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

   <script>
      //reply message
      function replyToMessage(token) {
         //console.log('token: ', token);

         // Remove 'message-active' from all messages
         document.querySelectorAll('.chat-message-block').forEach(function(messageBlock) {
            messageBlock.classList.remove('message-active');
         });

         // Add 'message-active' class to the clicked message
         let clickedMessage = document.querySelector(`[data-token='${token}']`);
         if (clickedMessage) {
            clickedMessage.classList.add('message-active');
         }

         // Store the selected message's token
         activeMessageToken = token;

         // Optionally store in localStorage to persist across page reloads
         localStorage.setItem('activeMessageToken', token);

         document.getElementById('chat-answerTo').value = token;
      }

      // Function to reapply 'message-active' class after refresh
      function reapplyActiveMessage() {
         const storedToken = localStorage.getItem('activeMessageToken');
         if (storedToken) {
            let activeMessage = document.querySelector(`[data-token='${storedToken}']`);
            if (activeMessage) {
               activeMessage.classList.add('message-active');
            }
         }
      }

      window.addEventListener("DOMContentLoaded", () => {
         let currentMessageCount = 0; // Store the current count of messages         

         let chatOpen = document.getElementById("chat-icon-toggle");
         let chatVisible = false; // Track chat visibility
         let messageCheckInterval; // Store the interval ID

         if (chatOpen) {
            chatOpen.addEventListener('click', (e) => {
               e.preventDefault();
               chatVisible = !chatVisible; // Toggle chat visibility

               if (chatVisible) {
                  // Fetch messages immediately when opening the chat
                  fetchNewMessages($('#chat-session-token-value').val(),
                     '<?= isset($_SESSION['role']) ? $_SESSION['role'] : null ?>');

                  // Set an interval to check for new messages every 5 seconds
                  messageCheckInterval = setInterval(checkMessageCountAndFetchMessages, 5000);
               } else {
                  // Clear the interval if the chat is closed
                  clearInterval(messageCheckInterval);
               }
            });
         }

         // Function to check the count of messages from the server and fetch new messages if count changes
         function checkMessageCountAndFetchMessages() {
            //console.log("Checking for new messages...");

            const sessionToken = $('#chat-session-token-value').val();
            const role = '<?= isset($_SESSION['role']) ? $_SESSION['role'] : null ?>';

            // Send request to get the message count based on session token and role
            $.ajax({
               url: 'get_message_count.php',
               method: 'POST',
               cache: false,
               contentType: 'application/json',
               data: JSON.stringify({
                  session_token: sessionToken,
                  role: role
               }),
               success: function(response) {
                  const newMessageCount = parseInt(response);
                  //console.log("New message count: " + newMessageCount);
                  //console.log("Current message count: " + currentMessageCount);

                  if (newMessageCount !== currentMessageCount) {
                     currentMessageCount = newMessageCount;
                     fetchNewMessages(sessionToken, role); // Fetch new messages if count changed
                  }

                  // Reapply active message after the update
                  reapplyActiveMessage();
               },
               error: function(xhr, status, error) {
                  console.error("Error fetching message count:", error);
               }
            });
         }

         // Function to fetch new messages from the server
         function fetchNewMessages(token, role) {
            // Fetch new messages
            $.ajax({
               url: 'get_new_messages.php',
               method: 'POST',
               contentType: 'application/json',
               data: JSON.stringify({
                  session_token: token,
                  role: role
               }),
               success: function(response) {
                  let messages = response;

                  if (messages && messages.length > 0) {
                     let chatBlockMessages = document.querySelector(".chat-form-content");
                     if (chatBlockMessages) {
                        chatBlockMessages.classList.add("active");
                     }
                  }

                  let htmlContent = '';

                  if (messages && Array.isArray(messages)) {
                     messages.forEach(msg => {
                        let userName = msg.user_name || "Anonymous";
                        let messageClass = '';

                        if (msg.user_role === 'admin') {
                           messageClass = 'chat-message-admin';
                        } else if (msg.user_role === 'manager') {
                           messageClass = 'chat-message-manager';
                        }

                        htmlContent += `
                    <div class="chat-message-block ${messageClass}" data-token="${msg.message_token}"
                        onclick="replyToMessage('${msg.message_token}')">
                       <div class="chat-message-block__header">
                          <div class="chat-message-block__header-left">
                             <div class="chat-message-avatar">${msg.user_name[0]}</div>
                             <div class="chat-message-name">${msg.user_name}</div>
                          </div>
                          <div class="chat-message-block__header-right">
                             <div class="chat-message-topic">${msg.message_topic}</div>
                             <div class="chat-message-date">${msg.created_at}</div>
                          </div>
                       </div>
                       <div class="chat-message-block__content">
                          <p>${msg.message_text}</p>
                       </div>
                    </div>`
                     });
                     $('#chat-message-list').html(htmlContent);
                  } else {
                     $('#chat-message-list').html(`<p>No new messages</p>`);
                  }

                  // After updating the message list, reapply the 'message-active' class
                  reapplyActiveMessage();

                  //scroll to last message
                  scrollToBottom();
               },
               error: function(xhr, status, error) {
                  console.error("AJAX error:", error);
               }
            });
         }
      });

      // Function to scroll to the bottom of the message list
      function scrollToBottom() {
         var messageList = document.getElementById('chat-message-list');
         if (messageList) {
            messageList.scroll({
               top: messageList.scrollHeight,
               behavior: 'smooth' // Add smooth scroll
            });
         }
      }
   </script>
</head>

<body>
   <!--==================== HEADER ====================-->
   <header class="header" id="header">
      <nav class="nav container">
         <a href="#" class="nav__logo">
            <i class="ri-book-3-line"></i> E-Book
         </a>
         <div class="nav__menu">
            <ul class="nav__list">
               <li class="nav__item">
                  <a href="#home " class="nav__link">
                     <i class="ri-home-line"></i>
                     <span>Home</span>
                  </a>
               </li>
               <li class="nav__item">
                  <a href="#featured" class="nav__link">
                     <i class="ri-book-3-line"></i>
                     <span>Featured</span>
                  </a>
               </li>
               <li class="nav__item">
                  <a href="#discount" class="nav__link">
                     <i class="ri-price-tag-3-line"></i>
                     <span>Discount</span>
                  </a>
               </li>
               <li class="nav__item">
                  <a href="#new" class="nav__link">
                     <i class="ri-bookmark-line"></i>
                     <span>New Books</span>
                  </a>
               </li>
               <li class="nav__item">
                  <a href="#testimonial" class="nav__link">
                     <i class="ri-message-3-line"></i>
                     <span>Testimonial</span>
                  </a>
               </li>
            </ul>
         </div>

         <div class="nav__actions">
            <i class="ri-search-line search-button" id="search-button"></i>

            <?php if (!isset($_SESSION['chat-login-name'])): ?>
               <i class="ri-user-line login-button" id="login-button"></i>
            <?php else: ?>
               <button class="login-button chat-login-btn" id="login-button">
                  <?php echo $_SESSION['chat-login-name']; ?>
               </button>
            <?php endif; ?>

            <i class="ri-moon-line change-theme" id="theme-button"></i>
         </div>
      </nav>
   </header>

   <!--==================== SEARCH ====================-->
   <div class="search" id="search-content">
      <form class="search__form">
         <i class="ri-search-line search__icon"></i>
         <input type="text" class="search__input" placeholder="What are you looking for?">
      </form>
      <i class="ri-close-line search__close" id="search-close"></i>
   </div>

   <!--==================== LOGIN ====================-->
   <div class="login grid" id="login-content">
      <?php if (!isset($_SESSION['chat-login-email'])): ?>
         <form class="login__form grid" method="POST" action="login.php">
            <h3 class="login__title">Log In</h3>
            <div class="login__group grid">
               <div>
                  <label for="login-email" class="login__label">Email</label>
                  <input type="email" class="login__input" placeholder="Write your email"
                     name="chat-login-email" id="chat-login-email" required>
               </div>
               <div>
                  <label for="login-pass" class="login__label">Password</label>
                  <input type="password" class="login__input" placeholder="Enter your password"
                     name="chat-login-pass" id="chat-login-pass" required>
               </div>
            </div>
            <div>
               <span class="login__signup">
                  You do not have an account? <a href="#">Sign Up</a>
               </span>
               <a href="#" class="login__forgot">
                  You forgot your password
               </a>
               <button type="submit" class="login__button button">Login</button>
            </div>
         </form>
      <?php else: ?>
         <form class="chat-manager-user" method="POST" action="logout.php">
            <span>Hello - <b><?php echo $_SESSION['chat-login-name']; ?></b></span>
            <button type="submit" class="chat-btn logout">logout</button>
         </form>
      <?php endif ?>

      <i class="ri-close-line login__close" id="login-close"></i>
   </div>

   <!--==================== MAIN ====================-->
   <main class="main">
      <!--==================== HOME ====================-->
      <section class="home section" id="home">
         <div class="home__container container grid">
            <div class="home__data">
               <h1 class="home__title">
                  Browse & <br> Select E-Books
               </h1>
               <p class="home__description">
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                  Delectus dolor exercitationem asperiores libero cupiditate
                  blanditiis facere repellendus suscipit dolores laudantium.
               </p>
               <a href="#" class="button">Explore Now</a>
            </div>
            <div class="home__images">
               <div class="home__swiper swiper">
                  <div class="swiper-wrapper">
                     <article class="home__article swiper-slide">
                        <img src="./assets/img/home-book-1.png" alt="book" class="home__img">
                     </article>
                     <article class="home__article swiper-slide">
                        <img src="./assets/img/home-book-2.png" alt="book" class="home__img">
                     </article>
                     <article class="home__article swiper-slide">
                        <img src="./assets/img/home-book-3.png" alt="book" class="home__img">
                     </article>
                     <article class="home__article swiper-slide">
                        <img src="./assets/img/home-book-4.png" alt="book" class="home__img">
                     </article>
                  </div>
               </div>
            </div>
         </div>
      </section>

      <!--==================== SERVICES ====================-->
      <section class="services section">
         <div class="services__container container grid">
            <article class="services__card">
               <i class="ri-truck-line"></i>
               <h3 class="services__title">Free sheeping</h3>
               <p class="services__description">
                  Order more than $100
               </p>
            </article>
            <article class="services__card">
               <i class="ri-lock-2-line"></i>
               <h3 class="services__title">Secure Payment</h3>
               <p class="services__description">
                  100% Secure Payment
               </p>
            </article>
            <article class="services__card">
               <i class="ri-customer-service-2-line"></i>
               <h3 class="services__title">24/7 Support</h3>
               <p class="services__description">
                  Call us anytime
               </p>
            </article>
         </div>
      </section>

      <!--==================== FEATURED ====================-->
      <section class="featured section" id="featured">
         <h2 class="section__title">Featured Books</h2>
         <div class="featured__container container">
            <div class="featured__swiper swiper">
               <div class="swiper-wrapper">
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-1.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-2.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-3.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-4.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-5.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-6.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-7.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-8.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-9.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
                  <article class="featured__card swiper-slide">
                     <img src="./assets/img/book-10.png" alt="feature" class="featured__img">
                     <h2 class="featured__title">Featured Book</h2>
                     <div class="featured__prices">
                        <span class="featured__discount">$11.99</span>
                        <span class="featured__price">$19.99</span>
                     </div>

                     <button class="button">Add To Cart</button>

                     <div class="featured__actions">
                        <button><i class="ri-search-line"></i></button>
                        <button><i class="ri-heart-3-line"></i></button>
                        <button><i class="ri-eye-line"></i></button>
                     </div>
                  </article>
               </div>

               <div class="swiper-button-prev">
                  <i class="ri-arrow-left-s-line"></i>
               </div>
               <div class="swiper-button-next">
                  <i class="ri-arrow-right-s-line"></i>
               </div>
            </div>
         </div>
      </section>

      <!--==================== DISCOUNT ====================-->
      <section class="discount section" id="discount">
         <div class="discount__container container grid">
            <div class="discount__data">
               <h2 class="discount__title section__title">Up to 50% Discount</h2>
               <p class="discount__description">
                  Lorem ipsum dolor sit amet consectetur adipisicing elit.
                  Recusandae est deserunt consectetur quaerat totam harum,
                  soluta odit magni impedit ipsa.
               </p>
               <a href="#" class="button">Shop Now</a>
            </div>

            <div class="discount__images">
               <img src="./assets/img/discount-book-1.png" alt="discount" class="discount__img-1">
               <img src="./assets/img/discount-book-2.png" alt="discount" class="discount__img-2">
            </div>
         </div>
      </section>

      <!--==================== NEW BOOKS ====================-->
      <section class="new section" id="new">
         <h2 class="section__title">New Books</h2>
         <div class="new__container container">
            <div class="new__swiper swiper">
               <div class="swiper-wrapper">
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-1.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-2.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-3.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-4.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-5.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-6.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-7.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-8.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-9.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-10.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
               </div>
            </div>
            <div class="new__swiper swiper">
               <div class="swiper-wrapper">
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-10.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-9.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-8.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-7.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-6.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-5.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-4.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-3.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-2.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
                  <a href="#" class="new__card swiper-slide">
                     <img src="./assets/img/book-1.png" alt="book" class="new__img">
                     <div>
                        <h2 class="new__title">New Book</h2>
                        <div class="new__prices">
                           <span class="new__discount">$7.99</span>
                           <span class="new__price">$14.99</span>
                        </div>
                        <div class="new__stars">
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-fill"></i>
                           <i class="ri-star-half-fill"></i>
                        </div>
                     </div>
                  </a>
               </div>
            </div>
         </div>
      </section>

      <!--==================== JOIN ====================-->
      <section class="join section">
         <div class="join__container">
            <img src="./assets/img/join-bg.jpg" alt="book" class="join__bg">
            <div class="join__data container grid">
               <h2 class="join__title section__title">
                  Subscribe to Recieve <br>
                  The Latest Updates
               </h2>
               <form class="join__form">
                  <input type="text" class="join__input" placeholder="Your email">
                  <button class="join__button button" type="submit">Subscribe</button>
               </form>
            </div>
         </div>
      </section>

      <!--==================== TESTIMONIAL ====================-->
      <section class="testimonial section" id="testimonial">
         <h2 class="section__title">Customer Opinions</h2>
         <div class="testimonial__container container">
            <div class="testimonial__swiper swiper">
               <div class="swiper-wrapper">
                  <article class="testimonial__card swiper-slide">
                     <img src="./assets/img/testimonial-perfil-1.png" alt="book" class="testimonial__img">
                     <h2 class="testimonial__title">Rial Loz</h2>
                     <p class="testimonial__description">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ex tempore id.
                     </p>
                     <div class="testimonial__stars">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-fill"></i>
                     </div>
                  </article>
                  <article class="testimonial__card swiper-slide">
                     <img src="./assets/img/testimonial-perfil-2.png" alt="book" class="testimonial__img">
                     <h2 class="testimonial__title">Rial Loz</h2>
                     <p class="testimonial__description">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ex tempore id.
                     </p>
                     <div class="testimonial__stars">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-fill"></i>
                     </div>
                  </article>
                  <article class="testimonial__card swiper-slide">
                     <img src="./assets/img/testimonial-perfil-3.png" alt="book" class="testimonial__img">
                     <h2 class="testimonial__title">Rial Loz</h2>
                     <p class="testimonial__description">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ex tempore id.
                     </p>
                     <div class="testimonial__stars">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-fill"></i>
                     </div>
                  </article>
                  <article class="testimonial__card swiper-slide">
                     <img src="./assets/img/testimonial-perfil-4.png" alt="book" class="testimonial__img">
                     <h2 class="testimonial__title">Rial Loz</h2>
                     <p class="testimonial__description">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ex tempore id.
                     </p>
                     <div class="testimonial__stars">
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-fill"></i>
                     </div>
                  </article>
               </div>
            </div>
         </div>
      </section>
   </main>

   <!--==================== FOOTER ====================-->
   <footer class="footer">
      <div class="footer__container container grid">
         <div>
            <a href="#" class="footer__logo">
               <i class="ri-book-3-line"></i> E-Book
            </a>
            <p class="footer__description">
               Find and explore the best <br>
               eBooks from all your <br>
               favourite writers.
            </p>
         </div>
         <div class="footer__data grid">
            <div>
               <h3 class="footer__title">About</h3>
               <ul class="footer__links">
                  <li>
                     <a href="#" class="footer__link">Awards</a>
                  </li>
                  <li>
                     <a href="#" class="footer__link">FAQs</a>
                  </li>
                  <li>
                     <a href="#" class="footer__link">Privacy Policy</a>
                  </li>
                  <li>
                     <a href="#" class="footer__link">Terms of Services</a>
                  </li>
               </ul>
            </div>
            <div>
               <h3 class="footer__title">Company</h3>
               <ul class="footer__links">
                  <li>
                     <a href="#" class="footer__link">Blogs</a>
                  </li>
                  <li>
                     <a href="#" class="footer__link">Community</a>
                  </li>
                  <li>
                     <a href="#" class="footer__link">Our Team</a>
                  </li>
                  <li>
                     <a href="#" class="footer__link">Help Center</a>
                  </li>
               </ul>
            </div>
            <div>
               <h3 class="footer__title">Contact</h3>
               <ul class="footer__links">
                  <li>
                     <address class="footer__info">
                        Sunny Street <br>
                        Kyiv 1234, Ukraine
                     </address>
                  </li>
                  <li>
                     <address class="footer__info">
                        mail@gmail.com <br>
                        1234-567-890
                     </address>
                  </li>
               </ul>
            </div>
            <div>
               <h3 class="footer__title">Social</h3>
               <div class="footer__social">
                  <a href="#" class="footer__social-link">
                     <i class="ri-facebook-circle-line"></i>
                  </a>
                  <a href="#" class="footer__social-link">
                     <i class="ri-instagram-line"></i>
                  </a>
                  <a href="#" class="footer__social-link">
                     <i class="ri-youtube-line"></i>
                  </a>
               </div>
            </div>
         </div>
      </div>
      <span class="footer__copy">
         &#169; All Rights Reserved. FreeTime. 2023.
      </span>
   </footer>

   <!--========== SCROLL UP ==========-->
   <a href="#" class="scrollup" id="scroll-up">
      <i class="ri-arrow-up-line"></i>
   </a>

   <!--========== TOGGLE CHAT BTN ==========-->
   <form class="chat-icon" id="chat-icon-toggle" method="POST">
      <input type="hidden" id="chat-session-token-value" name="chat-session-token-value" value="">
      <button type="submit">
         <box-icon name='chat'></box-icon>
      </button>
   </form>

   <!--========== CHAT FORM ==========-->
   <div class="chat-form-block" id="chat-form-block">
      <div class="chat-form-wrapper">
         <div class="chat-form-content" id="chat-message-list"></div>

         <form id="chatForm" method="POST">
            <input type="hidden" id="chat-hidden_user_id" name="hidden_user_id"
               value="<?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>" />

            <input type="hidden" id="chat-message-token" name="chat-message-token" value="">
            <input type="hidden" id="chat-session-token" name="chat-session-token" value="">

            <input type="hidden" id="chat-answerTo" name="chat-answer_to" value="" />

            <?php
            if (isset($_SESSION['chat-login-name'])) {
               $nameValue = $_SESSION['chat-login-name'];
            } else {
               $nameValue = "";
            }
            ?>

            <div class="chat-name-block">
               <label for="chat-message-user-name">Ваше ім'я:</label>
               <input type="text" name="chat-message-user-name" id="chat-message-user-name"
                  required placeholder="Як до вас звертатись?"
                  value="<?php echo ($nameValue); ?>">
            </div>

            <label for="chat-topic" id="chat-select-label">Тема (зі списку):</label>
            <div class="__select" data-state="" id="chat-topic">
               <div class="__select__title" data-default="Option 0">Topic 1</div>
               <div class="__select__content">
                  <input id="singleTopic0" class="__select__input" type="radio" name="singleTopic" />
                  <label for="singleTopic0" class="__select__label">Topic 0</label>
                  <input id="singleTopic1" class="__select__input" type="radio" name="singleTopic" />
                  <label for="singleTopic1" class="__select__label">Topic 1</label>
                  <input id="singleTopic2" class="__select__input" type="radio" name="singleTopic" />
                  <label for="singleTopic2" class="__select__label">Topic 2</label>
                  <input id="singleTopic3" class="__select__input" type="radio" name="singleTopic" />
                  <label for="singleTopic3" class="__select__label">Topic 3</label>
                  <input id="singleTopic4" class="__select__input" type="radio" name="singleTopic" />
                  <label for="singleTopic4" class="__select__label">Topic 4</label>
               </div>
            </div>

            <textarea id="chat-message" name="chat-message" rows="4" cols="50"
               placeholder="Наберіть текст повідомлення" required></textarea>

            <div class="chat-btn-block">
               <button class="chat-btn chat-form-close-btn" id="close-chat-btn">
                  Закрити
               </button>
               <button class="chat-btn" type="submit">
                  Відправити
               </button>
            </div>
         </form>
      </div>
   </div>

   <!--=============== SCROLLREVEAL ===============-->
   <script src="./assets/js/scrollreveal.min.js"></script>

   <!--=============== SWIPER JS ===============-->
   <script src="./assets/js/swiper-bundle.min.js"></script>

   <!--=============== box icons ===============-->
   <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>

   <!--=============== MAIN JS ===============-->
   <script src="./assets/js/main.js"></script>

   <!--=============== chat app JS ===============-->
   <script src="./assets/js/chat-app.js"></script>
</body>

</html>