    <!-- ===== FOOTER ===== -->
    <footer class="footer">
        <div class="footer-top">
            <div class="footer-inner">
                <div class="footer-col">
                    <h4>About</h4>
                    <ul>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">QuickKart Stories</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Corporate Information</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Help</h4>
                    <ul>
                        <li><a href="#">Payments</a></li>
                        <li><a href="#">Shipping</a></li>
                        <li><a href="#">Cancellation & Returns</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Report Infringement</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Consumer Policy</h4>
                    <ul>
                        <li><a href="#">Cancellation & Returns</a></li>
                        <li><a href="#">Terms Of Use</a></li>
                        <li><a href="#">Security</a></li>
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Sitemap</a></li>
                        <li><a href="#">Grievance Redressal</a></li>
                        <li><a href="#">EPR Compliance</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Social</h4>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">YouTube</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Mail Us:</h4>
                    <p>QuickKart Internet Private Limited,<br>
                    Buildings Alyssa, Begonia &<br>
                    Clove Embassy Tech Village,<br>
                    Outer Ring Road, Devarabeesanahalli Village,<br>
                    Bengaluru, 560103,<br>
                    Karnataka, India</p>
                </div>
                <div class="footer-col">
                    <h4>Registered Office:</h4>
                    <p>QuickKart Internet Private Limited,<br>
                    Buildings Alyssa, Begonia &<br>
                    Clove Embassy Tech Village,<br>
                    Outer Ring Road, Devarabeesanahalli Village,<br>
                    Bengaluru, 560103,<br>
                    Karnataka, India<br>
                    CIN : U51109KA2012PTC066107<br>
                    Telephone: <a href="tel:044-45614700" style="color:#2874f0;">044-45614700</a></p>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-container footer-bottom-inner">
                <span>Become a Seller</span>
                <span>Advertise</span>
                <span>Gift Cards</span>
                <span>Help Center</span>
                <span>&copy; <?php echo date('Y'); ?> QuickKart Clone</span>
                <span><svg viewBox="0 0 56 20" width="56" height="20"><g fill="#fff"><path d="M33.5 0C37.1 0 40 2.9 40 6.5S37.1 13 33.5 13 27 10.1 27 6.5 29.9 0 33.5 0zM22 20c0-3.3 2.7-6 6-6h11c3.3 0 6 2.7 6 6H22z"/><path fill="#2874f0" d="M33.5 2C36 2 38 4 38 6.5S36 11 33.5 11 29 9 29 6.5 31 2 33.5 2zM24 18c0-2.2 1.8-4 4-4h11c2.2 0 4 1.8 4 4H24z"/></g></svg></span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo getBaseUrl(); ?>/js/main.js"></script>
    <script src="<?php echo getBaseUrl(); ?>/js/wishlist.js"></script>

    <!-- ===== AI SHOPPING ASSISTANT ===== -->
    <?php
    $chatProducts = [];
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT p.name, p.slug, p.price, p.brand, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1");
        $chatProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $chatProducts = [];
    }
    ?>
    <script>
        window.shopProducts = <?php echo json_encode($chatProducts); ?>;
        window.shopBaseUrl = "<?php echo getBaseUrl(); ?>";
    </script>

    <div id="ai-assistant-widget" style="position: fixed; bottom: 24px; right: 24px; z-index: 10000; font-family: 'Roboto', sans-serif;">
        <!-- FAB Button -->
        <button id="ai-chat-fab" style="width: 56px; height: 56px; border-radius: 28px; background: #2874f0; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,0.20); transition: transform 0.25s, background-color 0.2s; outline: none;">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </button>
        
        <!-- Chat Window -->
        <div id="ai-chat-window" style="display: none; width: 340px; height: 450px; background: #fff; border-radius: 8px; box-shadow: 0 8px 30px rgba(0,0,0,0.18); flex-direction: column; overflow: hidden; position: absolute; bottom: 70px; right: 0; border: 1px solid #e2e8f0; transition: all 0.3s ease;">
            <!-- Header -->
            <div style="background: #2874f0; color: #fff; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 8px; height: 8px; background: #4caf50; border-radius: 50%;"></div>
                    <span style="font-weight: 500; font-size: 14px; letter-spacing: 0.2px;">QuickKart AI Assistant</span>
                </div>
                <button id="ai-chat-close" style="background: none; border: none; color: #fff; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            
            <!-- Messages Box -->
            <div id="ai-chat-messages" style="flex: 1; padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #f8fafc; font-size: 13px; line-height: 1.45; color: #334155;">
                <div style="align-self: flex-start; background: #f1f5f9; border-radius: 6px; padding: 8px 12px; max-width: 85%; border: 1px solid #e2e8f0;">
                    Hi! I am your QuickKart Shopping Assistant. How can I help you today? You can ask me to:
                    <div style="margin-top: 6px; padding-left: 12px;">
                        • Show products (e.g. <em>"Show me laptops"</em>)<br>
                        • Check prices (e.g. <em>"What is the price of the Sofa?"</em>)<br>
                        • Ask about shipping or returns
                    </div>
                </div>
            </div>
            
            <!-- Input Form -->
            <form id="ai-chat-form" style="display: flex; padding: 10px; border-top: 1px solid #e2e8f0; background: #fff; margin:0;" onsubmit="event.preventDefault(); sendChatMessage();">
                <input type="text" id="ai-chat-input" placeholder="Ask something..." style="flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none; transition: border-color 0.2s;" required autocomplete="off">
                <button type="submit" style="background: #fb641b; border: none; color: #fff; border-radius: 4px; padding: 0 16px; margin-left: 8px; cursor: pointer; font-weight: 600; font-size: 13px; transition: background 0.15s;">Send</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var fab = document.getElementById('ai-chat-fab');
        var win = document.getElementById('ai-chat-window');
        var close = document.getElementById('ai-chat-close');
        var input = document.getElementById('ai-chat-input');
        var messages = document.getElementById('ai-chat-messages');

        if (!fab || !win || !close || !input || !messages) return;

        fab.addEventListener('click', function() {
            if (win.style.display === 'none' || win.style.display === '') {
                win.style.display = 'flex';
                fab.style.transform = 'scale(0.9) rotate(90deg)';
                input.focus();
            } else {
                win.style.display = 'none';
                fab.style.transform = 'scale(1) rotate(0deg)';
            }
        });

        close.addEventListener('click', function() {
            win.style.display = 'none';
            fab.style.transform = 'scale(1) rotate(0deg)';
        });

        window.sendChatMessage = function() {
            var text = input.value.trim();
            if (text === '') return;

            appendMessage(text, 'user');
            input.value = '';

            var typing = document.createElement('div');
            typing.style.cssText = 'align-self: flex-start; background: #f1f5f9; border-radius: 6px; padding: 8px 12px; max-width: 85%; font-style: italic; color: #64748b; border: 1px solid #e2e8f0;';
            typing.textContent = 'Typing...';
            messages.appendChild(typing);
            messages.scrollTop = messages.scrollHeight;

            setTimeout(function() {
                messages.removeChild(typing);
                var reply = getAIResponse(text);
                appendMessage(reply, 'bot');
            }, 600);
        };

        function appendMessage(text, sender) {
            var msg = document.createElement('div');
            if (sender === 'user') {
                msg.style.cssText = 'align-self: flex-end; background: #2874f0; color: #fff; border-radius: 6px; padding: 8px 12px; max-width: 85%;';
            } else {
                msg.style.cssText = 'align-self: flex-start; background: #f1f5f9; border-radius: 6px; padding: 8px 12px; max-width: 85%; border: 1px solid #e2e8f0;';
            }
            var formattedText = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" style="color: ' + (sender === 'user' ? '#fff' : '#2874f0') + '; text-decoration: underline; font-weight: 500;">$1</a>');
            msg.innerHTML = formattedText.replace(/\n/g, '<br>');
            messages.appendChild(msg);
            messages.scrollTop = messages.scrollHeight;
        }

        function getAIResponse(query) {
            query = query.toLowerCase();
            var products = window.shopProducts || [];
            var baseUrl = window.shopBaseUrl || '';

            if (query.match(/^(hi|hello|hey|greetings)/)) {
                return "Hello! I am your QuickKart Shopping Assistant. How can I help you today? You can ask me to search for categories like Mobiles, Laptops, or specific products!";
            }

            if (query.indexOf('category') !== -1 || query.indexOf('categories') !== -1) {
                return "We have these categories in our store:\n" +
                       "• **Mobiles**\n" +
                       "• **Laptops**\n" +
                       "• **Fashion**\n" +
                       "• **Electronics**\n" +
                       "• **Home & Furniture**\n" +
                       "• **Beauty**\n" +
                       "• **Books**\n" +
                       "• **Sports**\n" +
                       "You can filter them by clicking the top panel tabs!";
            }

            var matchCat = '';
            var cats = ['mobile', 'laptop', 'fashion', 't-shirt', 'clothing', 'electronic', 'speaker', 'headphone', 'sofa', 'furniture', 'beauty', 'lipstick', 'book', 'sport'];
            for (var i = 0; i < cats.length; i++) {
                if (query.indexOf(cats[i]) !== -1) {
                    matchCat = cats[i];
                    break;
                }
            }

            if (matchCat) {
                var matches = [];
                var searchSlug = matchCat;
                if (searchSlug === 'mobile') searchSlug = 'mobiles';
                if (searchSlug === 'laptop') searchSlug = 'laptops';
                if (searchSlug === 't-shirt' || searchSlug === 'clothing') searchSlug = 'fashion';
                if (searchSlug === 'speaker' || searchSlug === 'headphone') searchSlug = 'electronics';
                if (searchSlug === 'sofa') searchSlug = 'home-furniture';
                if (searchSlug === 'lipstick') searchSlug = 'beauty';

                for (var j = 0; j < products.length; j++) {
                    var p = products[j];
                    var pCat = (p.category || '').toLowerCase();
                    var pName = (p.name || '').toLowerCase();
                    if (pCat.indexOf(searchSlug) !== -1 || pName.indexOf(matchCat) !== -1) {
                        matches.push(p);
                    }
                }

                if (matches.length > 0) {
                    var reply = "Here are matching items in the store:\n";
                    for (var k = 0; k < matches.length; k++) {
                        var item = matches[k];
                        var itemUrl = baseUrl + '/products/product.php?slug=' + item.slug;
                        reply += "• **" + item.name + "** (" + item.brand + ") - ₹" + Number(item.price).toLocaleString('en-IN') + " \n  [View Details](" + itemUrl + ")\n";
                    }
                    return reply;
                }
            }

            var foundProduct = null;
            for (var j = 0; j < products.length; j++) {
                var pName = products[j].name.toLowerCase();
                if (query.indexOf(pName) !== -1) {
                    foundProduct = products[j];
                    break;
                }
            }

            if (foundProduct) {
                var itemUrl = baseUrl + '/products/product.php?slug=' + foundProduct.slug;
                return "Yes, we have the **" + foundProduct.name + "** (" + foundProduct.brand + ") in stock! It is priced at ₹" + Number(foundProduct.price).toLocaleString('en-IN') + ".\n[Check it out here](" + itemUrl + ").";
            }

            if (query.indexOf('order') !== -1 || query.indexOf('track') !== -1) {
                return "You can check the status of your orders on your [Orders Dashboard](" + baseUrl + "/customer/orders.php).";
            }

            if (query.indexOf('pay') !== -1 || query.indexOf('payment') !== -1 || query.indexOf('cod') !== -1 || query.indexOf('upi') !== -1) {
                return "We support secure payments! You can pay using **Cash on Delivery (COD)** or **Online Payment** (Credit/Debit Card, UPI ID) during checkout.";
            }

            if (query.indexOf('deliver') !== -1 || query.indexOf('shipping') !== -1 || query.indexOf('free') !== -1) {
                return "We offer **Free Delivery** on orders of ₹499 and above. For orders below ₹499, a delivery charge of ₹40 applies.";
            }

            if (query.indexOf('return') !== -1 || query.indexOf('refund') !== -1 || query.indexOf('cancel') !== -1) {
                return "We offer an easy 10-day cancellation and return policy. Start a return request from your Orders page.";
            }

            return "I'm not sure I understand. You can ask me things like:\n" +
                   "• *Show me laptops*\n" +
                   "• *What is the price of the Sofa?*\n" +
                   "• *How can I track my order?*\n" +
                   "• *What are the payment options?*";
        }
    });
    </script>
</body>
</html>
