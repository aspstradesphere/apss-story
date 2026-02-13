
// APSS Smart Chat Assistant ("Tanishtha")
// Dynamically fetches story_content.json to answer questions.

(function () {
    // 1. Setup UI
    const css = `
        #apss-chat-widget {
            position: fixed; bottom: 20px; right: 20px; z-index: 10000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex; flex-direction: column; align-items: flex-end;
        }
        #apss-chat-btn {
            background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%);
            color: white; border: none; border-radius: 50%;
            width: 60px; height: 60px; font-size: 24px; cursor: pointer;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3); 
            transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center;
        }
        #apss-chat-btn:hover { 
            transform: scale(1.1); 
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.5);
        }
        
        #apss-chat-window {
            width: 350px; height: 500px; background: white; border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2); overflow: hidden;
            display: none; flex-direction: column; margin-bottom: 15px;
            border: 1px solid #eee;
        }
        
        .chat-header {
            background: #2E7D32; color: white; padding: 15px; display: flex;
            justify-content: space-between; align-items: center; font-weight: bold;
        }
        .chat-close { cursor: pointer; font-size: 20px; }
        
        .chat-messages {
            flex: 1; padding: 15px; overflow-y: auto; background: #f9f9f9;
            font-size: 14px; line-height: 1.4;
        }
        .msg { margin-bottom: 15px; max-width: 85%; }
        .msg.bot { align-self: flex-start; background: #e8f5e9; padding: 10px; border-radius: 10px 10px 10px 0; color: #333; }
        .msg.user { align-self: flex-end; background: #fff3e0; padding: 10px; border-radius: 10px 10px 0 10px; margin-left: auto; text-align: right; color: #333; }
        
        .chat-input-area {
            padding: 10px; border-top: 1px solid #eee; display: flex; gap: 5px; background: white;
        }
        .chat-input-area input {
            flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px; outline: none;
        }
        .chat-input-area button {
            background: #D98600; color: white; border: none; padding: 0 15px; border-radius: 20px; cursor: pointer;
        }
        
        /* Typing indicator */
        .typing { font-style: italic; color: #888; font-size: 12px; margin-bottom: 5px; display: none; }
    `;

    // Inject CSS
    const style = document.createElement('style');
    style.innerHTML = css;
    document.head.appendChild(style);

    // Inject HTML
    const widget = document.createElement('div');
    widget.id = 'apss-chat-widget';
    widget.innerHTML = `
        <div id="apss-chat-window">
            <div class="chat-header">
                <span><i class="fas fa-robot"></i> APSS Assistant</span>
                <span class="chat-close" onclick="toggleChat()">&times;</span>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div class="msg bot">Namaste! I am Tanishtha. How can I help you with our premium agro-products today?</div>
            </div>
            <div class="typing" id="typing-indicator">Tanishtha is typing...</div>
            <div class="chat-input-area">
                <input type="text" id="chat-input" placeholder="Type a message..." onkeypress="handleKey(event)">
                <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <button id="apss-chat-btn" onclick="toggleChat()"><i class="fas fa-headset"></i></button>
    `;
    document.body.appendChild(widget);

    // 2. Logic & Knowledge Base
    let kb = {
        products: [],
        general: {
            "contact": "You can reach us specifically via email at <b>sales@apsstradesphere.com</b> or call +91 87000 72233.",
            "location": "We are based in <b>Bangalore, India</b>, with sourcing hubs across India:<br>• <b>Bihar</b> - Makhana (Fox Nuts)<br>• <b>Karnataka</b> - Areca Palm Leaf Plates<br>• <b>Salem & Erode</b> - Turmeric and Spices<br>• <b>Guntur</b> - Red Chilli",
            "shipping": "We export globally to over 35 countries including USA, UK, Europe, and UAE.",
            "catalog": "You can download our full product catalogs directly from the 'Our Product Catalogs' section on this page.",
            "price": "For accurate pricing, please share your requirement details (Quantity, Destination) via email.",
            "sample": "We do provide samples for serious B2B inquiries. Please contact our sales team.",
            "moq": "Our Minimum Order Quantity (MOQ) varies by product but typically starts from a pallet for air cargo or full container loads.",
            "payment": "We accept various payment terms including L/C and T/T depending on the order value.",
            "founded": "APSS TradeSphere was founded in <b>2025</b> in Bangalore by 2 passionate owners committed to exporting India's finest agro-products with zero compromise on quality.",
            "company": "We are APSS TradeSphere - a Bangalore-based premium agro-export company founded by <b>Moni Singh</b> and <b>Ayodhaya Singh</b>. We specialize in sustainable, high-quality products sourced directly from India's best farming regions.",
            "about": "APSS TradeSphere was founded in Bangalore by <b>Moni Singh</b> and <b>Ayodhaya Singh</b> with one goal: To export India's finest agro-products with zero compromise on quality. We source from the best regions and maintain strict quality standards.",
            "team": "Our company is led by 2 owners:<br>• <b>Moni Singh</b> - Co-Owner<br>• <b>Ayodhaya Singh</b> - Co-Owner<br><br>Supported by our dedicated team:<br>• <b>Sujit Singh</b> - Quality Assurance<br>• <b>Sanjeev Singh</b> - Sales & Client Relationship Manager<br>• <b>Anu Rao</b> - Sales",
            "owner": "APSS TradeSphere is owned by <b>Moni Singh</b> and <b>Ayodhaya Singh</b>, who bring extensive expertise in agro-export and quality management.",
            "ceo": "APSS TradeSphere is owned and managed by <b>Moni Singh</b> and <b>Ayodhaya Singh</b>. As a partnership firm, we don't have a traditional CEO structure - both owners jointly lead the company.",
            "sales": "Our sales team includes:<br>• <b>Sanjeev Singh</b> - Sales & Client Relationship Manager<br>• <b>Anu Rao</b> - Sales<br><br>For inquiries, contact <b>sales@apsstradesphere.com</b> or call +91 87000 72233.",
            "qa": "<b>Sujit Singh</b> handles our Quality Assurance. We maintain strict 3-stage quality control to ensure every product meets international standards.",
            "quality": "We maintain strict 3-stage quality control: inspection at farm, factory processing, and pre-shipment verification. All products are lab-tested and certified.",
            "certification": "Our products meet international food safety standards. We provide necessary certifications including FSSAI, and can arrange additional certifications as per destination requirements.",
            "sustainable": "Sustainability is at our core. From biodegradable Areca plates to organic farming practices for our spices, we prioritize eco-friendly solutions.",
            "export": "Yes! We export to over 35 countries worldwide including USA, UK, Europe, UAE, and more. We handle all export documentation and logistics.",
            "sourcing": "We source directly from India's premium regions:<br>• <b>Makhana</b> - Bihar<br>• <b>Areca Palm Leaf</b> - Karnataka<br>• <b>Turmeric & Spices</b> - Salem & Erode<br>• <b>Red Chilli</b> - Guntur",
            "bihar": "We source premium <b>Makhana (Fox Nuts)</b> from Bihar, known for producing the finest quality Phool Makhana in India.",
            "karnataka": "Our <b>Areca Palm Leaf Plates</b> are sourced from Karnataka, where sustainable areca palm cultivation is a traditional practice.",
            "salem": "We source high-quality <b>Turmeric and Spices</b> from Salem and Erode regions, renowned for their premium curcumin content and authentic flavor.",
            "erode": "Erode is famous for its <b>GI-tagged Turmeric</b> with exceptional curcumin levels. We source directly from this premium region.",
            "guntur": "We source fiery <b>Red Chilli</b> from Guntur, Andhra Pradesh - the spice capital of India known for its bold, vibrant chillies."
        }
    };

    // Load Data
    fetch('data/story_content.json')
        .then(res => res.json())
        .then(data => {
            if (data.products) {
                kb.products = data.products;
                console.log("APSS Chat: Knowledge Base Loaded", kb.products.length + " products");
            }
        })
        .catch(err => console.error("Chat Error:", err));

    // Global toggle function
    window.toggleChat = function () {
        const win = document.getElementById('apss-chat-window');
        const btn = document.getElementById('apss-chat-btn');
        if (win.style.display === 'flex') {
            win.style.display = 'none';
            btn.style.display = 'flex';
        } else {
            win.style.display = 'flex';
            btn.style.display = 'none';
            // Auto focus
            setTimeout(() => document.getElementById('chat-input').focus(), 100);
        }
    };

    // --- Mulitlingual Support Helpers ---

    async function detectAndTranslateToEnglish(text) {
        try {
            // using MyMemory API for demo purposes
            const res = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=Autodetect|en`);
            const data = await res.json();

            if (data.responseData) {
                // If confidence is low, it might be English or mixed. 
                // matches[0].id usually holds the source lang code (e.g. "fr-FR")
                let sourceLang = "en";
                if (data.matches && data.matches.length > 0) {
                    sourceLang = data.matches[0].segment; // sometimes obscure
                    // Better: parsing the detected language is tricky with this free API specifically for 'Autodetect'.
                    // MyMemory often returns the pair in data.responseData.match
                }

                // MyMemory detected language hack:
                // We will blindly trust it translates TO English.
                // We need to know the source language to translate BACK.
                // Valid strategy: accept the english text.
                // For the *return* trip, we might not know the exact code unless we ask explicitly or infer.
                // Simplification for reliability: Prompt user to select language? No, user asked for auto.

                // Alternative: just returning the english text
                return {
                    englishText: data.responseData.translatedText,
                    detectedLang: data.responseData.detectedSourceLanguage || 'en'
                };
            }
        } catch (e) {
            console.error("Translation Error", e);
        }
        return { englishText: text, detectedLang: 'en' };
    }

    async function translateToUserLang(text, targetLang) {
        if (!targetLang || targetLang.startsWith('en')) return text;
        try {
            const res = await fetch(`https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=en|${targetLang}`);
            const data = await res.json();
            return data.responseData.translatedText || text;
        } catch (e) {
            return text;
        }
    }

    window.handleKey = function (e) {
        if (e.key === 'Enter') sendMessage();
    };

    window.sendMessage = async function () {
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text) return;

        // 1. Show User Msg immediately
        addMsg(text, 'user');
        input.value = '';
        showTyping(true);

        // 2. Process Multilingual
        // (Disabled Translation API for stability - reverting to direct English processing)
        /*
        try {
            // A. Detect & Translate to English
            const { englishText, detectedLang } = await detectAndTranslateToEnglish(text);
            console.log(`Detected: ${detectedLang}, English: ${englishText}`);

            // B. Generate Logic (runs on English text)
            const englishReply = generateReply(englishText);

            // C. Translate Reply back to User Language
            let finalReply = englishReply;
            if (detectedLang && !detectedLang.startsWith('en')) {
                const plainText = englishReply.replace(/<[^>]*>?/gm, '');
                const translatedPlain = await translateToUserLang(plainText, detectedLang);
                finalReply = translatedPlain;
                finalReply += `<br><br><span style="font-size:10px; color:#888;">(Translated from: ${englishReply.substring(0, 50)}...)</span>`;
            }

            // D. Display
            addMsg(finalReply, 'bot');

        } catch (err) {
            console.error(err);
            // Fallback
            addMsg(generateReply(text), 'bot');
        } finally {
            showTyping(false);
        }
        */

        // Direct Processing (Stable)
        setTimeout(() => {
            const reply = generateReply(text);
            addMsg(reply, 'bot');
            showTyping(false);
        }, 600);
    };

    function addMsg(text, type) {
        const div = document.createElement('div');
        div.className = 'msg ' + type;
        div.innerHTML = text;
        const container = document.getElementById('chat-messages');
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function showTyping(show) {
        document.getElementById('typing-indicator').style.display = show ? 'block' : 'none';
    }

    function generateReply(input) {
        // Safe check
        if (!input) return "I didn't catch that.";

        const lower = input.toLowerCase();

        // 1. Product Listing Questions
        if (lower.includes('what') && (lower.includes('product') || lower.includes('sell') || lower.includes('offer') || lower.includes('have'))) {
            let reply = "<b>Our Premium Product Range:</b><br><br>";
            const visibleProducts = kb.products.filter(p => !p.hidden);

            if (visibleProducts.length > 0) {
                visibleProducts.forEach(p => {
                    reply += `<b>• ${p.name}</b><br>${p.description}<br><br>`;
                });
                reply += "Ask me about any specific product for more details!";
            } else {
                reply = "We offer premium agro-products including Areca Palm Leaf Plates, Turmeric, and Makhana. Ask me about any of these!";
            }
            return reply;
        }

        // 2. Smart Keyword Matching for General Questions
        const patterns = [
            { keywords: ['your name', 'ur name', 'who are you', 'what are you', 'call you'], response: "I'm <b>Tanishtha</b>, your virtual assistant for APSS TradeSphere! I'm here to help you learn about our premium agro-products and answer any questions you have. 😊" },
            { keywords: ['thank', 'thanks'], response: "You're welcome! Feel free to ask me anything else about our products or services. 😊" },
            { keywords: ['help', 'assist', 'support'], response: "I'm here to help! You can ask me about:<br>• Our products (Areca, Turmeric, Makhana)<br>• Sourcing locations<br>• Company information<br>• Team members<br>• Pricing & MOQ<br>• Export capabilities<br><br>What would you like to know?" },
            { keywords: ['how are you', 'how do you do', 'how r u'], response: "I'm doing great, thank you for asking! How can I assist you with our premium agro-products today? 😊" },
            { keywords: ['your age', 'how old'], response: "I'm a virtual assistant, so I don't have an age! But I'm always up-to-date with the latest information about our products. How can I help you today? 😊" },
            { keywords: ['speak', 'language', 'hindi', 'english'], response: "I can understand and communicate in **multiple languages**! at least I try to ... 😊" },
            { keywords: ['bye', 'goodbye', 'see you'], response: "Thank you for chatting with me! If you have more questions, feel free to reach out anytime at <b>sales@apsstradesphere.com</b> or call +91 87000 72233. Have a great day! 🙏" },

            // Business information patterns
            { keywords: ['when', 'found', 'establish', 'start', 'form'], response: kb.general.founded },
            { keywords: ['where', 'office', 'headquarter', 'address'], response: kb.general.location },
            { keywords: ['ceo', 'chief executive'], response: kb.general.ceo },
            { keywords: ['owner', 'founder', 'partner', 'who run', 'who lead'], response: kb.general.team },
            { keywords: ['sales', 'sell', 'client relation', 'business development'], response: kb.general.sales },
            { keywords: ['qa', 'quality assurance', 'quality control', 'qc'], response: kb.general.qa },
            { keywords: ['about', 'tell me', 'history', 'background', 'story'], response: kb.general.about },
            { keywords: ['quality', 'standard'], response: kb.general.quality },
            { keywords: ['certif', 'fssai', 'license'], response: kb.general.certification },
            { keywords: ['sustain', 'eco', 'green', 'environment'], response: kb.general.sustainable },
            { keywords: ['export', 'ship', 'deliver', 'international'], response: kb.general.export },
            { keywords: ['contact', 'email', 'phone', 'call', 'reach'], response: kb.general.contact },
            { keywords: ['price', 'cost', 'rate', 'quotation'], response: kb.general.price },
            { keywords: ['sample'], response: kb.general.sample },
            { keywords: ['moq', 'minimum order', 'minimum quantity'], response: kb.general.moq },
            { keywords: ['payment', 'terms', 'lc', 't/t'], response: kb.general.payment },
            { keywords: ['catalog', 'catelog', 'catalogue', 'brochure', 'pdf', 'download'], response: kb.general.catalog },

            // Hindi Fallback Patterns (if translation fails)
            { keywords: ['kya naam', 'naam kya', 'kon ho', 'kaun ho', 'tumhara naam', 'aapka naam'], response: "Mera naam <b>Tanishtha</b> hai! Main APSS TradeSphere ki virtual assistant hoon. 😊" },
            { keywords: ['kaise ho', 'kya haal', 'kaisa chal raha'], response: "Main bilkul thik hoon, dhanyavad! Main aapki madad kaise kar sakti hoon? 😊" },
            { keywords: ['kya karte', 'kya kaam', 'company kya', 'kya bechte', 'kya hai'], response: "Hum premium agro-products export karte hain jaise <b>Areca Plates, Haldi, aur Makhana</b>. Aap kis product ke baare mein jaanna chahenge?" },
            { keywords: ['madad', 'sahayata', 'jankari'], response: "Main aapki puri madad karungi! Aap humare products, prices, ya export ke baare mein puch sakte hain." },
            { keywords: ['price', 'daam', 'kimat', 'rate', 'kitne ka', 'paise'], response: "Price quantity aur destination par depend karta hai. Kripya sales@apsstradesphere.com par email karein." }
        ];

        for (const pattern of patterns) {
            let matchCount = 0;
            for (const kw of pattern.keywords) {
                if (lower.includes(kw)) matchCount++;
            }
            if (matchCount > 0) return pattern.response;
        }

        // 3. Product Specifics
        let foundProduct = null;
        for (const p of kb.products) {
            if (lower.includes(p.id) || lower.includes(p.name.toLowerCase())) { foundProduct = p; break; }
            if (p.id === 'makhana' && (lower.includes('fox nut') || lower.includes('lotus seed') || lower.includes('snack'))) foundProduct = p;
            if (p.id === 'areca' && (lower.includes('plate') || lower.includes('palm') || lower.includes('tableware'))) foundProduct = p;
            if (p.id === 'turmeric' && (lower.includes('haldi') || lower.includes('curcumin') || lower.includes('yellow'))) foundProduct = p;
            if (p.id === 'chilli' && (lower.includes('chili') || lower.includes('spice') || lower.includes('hot'))) foundProduct = p;
            if (p.id === 'blackpepper' && (lower.includes('black pepper') || lower.includes('pepper') || lower.includes('king'))) foundProduct = p;
        }

        if (foundProduct) {
            if (lower.includes('spec') || lower.includes('detail') || lower.includes('pack')) {
                if (foundProduct.specs) {
                    let s = "<b>Specifications for " + foundProduct.name + ":</b><br>";
                    foundProduct.specs.forEach(sp => s += `- ${sp.title}: ${sp.desc}<br>`);
                    return s;
                }
            }

            let reply = `<b>${foundProduct.name}</b><br>${foundProduct.description}<br><br>`;
            if (foundProduct.hidden) {
                reply += "<i>(Note: This product is currently in our upcoming roadmap).</i>";
            } else {
                if (foundProduct.long_description) {
                    const sentences = foundProduct.long_description.split(/[.!?]+/);
                    const summary = sentences.slice(0, 2).join('. ').trim();
                    const truncated = summary.length > 250 ? summary.substring(0, 250) + '...' : summary + '.';
                    reply += `${truncated}<br><br>`;
                }
                if (foundProduct.highlights && foundProduct.highlights.length > 0) {
                    reply += "<b>Key Features:</b><br>";
                    foundProduct.highlights.slice(0, 3).forEach(h => {
                        reply += `• ${h.title}<br>`;
                    });
                    reply += "<br>";
                }
                reply += `<i>💡 Ask me about "specs for ${foundProduct.name}" or contact our sales team for pricing!</i>`;
            }
            return reply;
        }

        // 4. Greeting
        const greetings = ['hi', 'hello', 'namaste', 'hey'];
        const words = lower.split(/\s+/);
        if (greetings.some(g => words.includes(g))) {
            return "Namaste! How can I assist you with our sustainable products today?";
        }

        // 5. Default
        return "I'm not sure about that specific detail yet. Please email <b>sales@apsstradesphere.com</b> for a detailed response, or ask about our main products: Areca, Turmeric, or Makhana.";
    }

})();
