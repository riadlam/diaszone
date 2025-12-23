@extends('layouts.app')

@section('title', 'Privacy Policy - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-7xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4">
                PRIVACY POLICY
            </h1>
            <p class="text-gray-600">UPDATED AND EFFECTIVE AS OF <strong>{{ date('d.m.Y') }}</strong></p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <!-- Sidebar Navigation - Fixed on Left -->
            <aside id="privacy-sidebar" class="w-full lg:w-64 flex-shrink-0 lg:order-1">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Table of Contents</h3>
                    <nav class="space-y-2">
                        <a href="#information-we-collect" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Information We Collect</a>
                        <a href="#why-we-collect" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Why We Collect Data</a>
                        <a href="#children-privacy" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Children's Privacy</a>
                        <a href="#when-we-collect" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">When We Collect</a>
                        <a href="#how-we-collect" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">How We Collect</a>
                        <a href="#what-we-collect" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">What We Collect</a>
                        <a href="#how-we-use" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">How We Use</a>
                        <a href="#legal-bases" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Legal Bases</a>
                        <a href="#data-storage" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Data Storage</a>
                        <a href="#your-rights" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Your Rights</a>
                        <a href="#disclosure" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Disclosure</a>
                        <a href="#international-transfer" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">International Transfer</a>
                        <a href="#withdraw-consent" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Withdraw Consent</a>
                        <a href="#policy-changes" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Policy Changes</a>
                        <a href="#contact" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Contact Us</a>
                    </nav>
                </div>
            </aside>
            
            <!-- Spacer to maintain layout when sidebar becomes sticky -->
            <div id="sidebar-spacer" class="hidden lg:block w-64 flex-shrink-0"></div>

            <!-- Content -->
            <div class="flex-1 min-w-0 lg:order-2">
                <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12 space-y-8">
                    <div class="prose prose-lg max-w-none">
                        <!-- Introduction -->
                        <section id="information-we-collect" class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Information We Collect From You</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                DiasZone and its affiliates (collectively, "<strong>DiasZone</strong>", "<strong>we</strong>", "<strong>us</strong>", or "<strong>our</strong>") are firmly committed to protecting the privacy of our customers and users ("<strong>you</strong>" or "<strong>your</strong>") and the Personal Information you entrust to us. This Privacy Policy explains what Personal Information we collect, how we use it, and to whom it may be disclosed.
                            </p>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                "<strong>Personal Information</strong>" means information about an identifiable individual and includes information you have provided to us or that we have collected from other sources. Such information may include, without limitation, your name, address, age, gender, personal financial records, and identification numbers, to the extent permitted by applicable laws.
                            </p>
                            <p class="text-gray-700 leading-relaxed">
                                This Privacy Policy governs your use of the website located at <strong>https://diaszone.com/</strong> and any other websites owned or operated by DiasZone that reference this Privacy Policy (collectively, the "<strong>DiasZone Sites</strong>"). Please read this Privacy Policy carefully. By accessing, using, or visiting the DiasZone Sites, you acknowledge that you have read, understood, and agree to be bound by the terms of this Privacy Policy. If you do not agree with any provision of this Privacy Policy, you must immediately cease all use of, access to, and/or visitation of the DiasZone Sites.
                            </p>
                        </section>

                        <!-- Why We Collect Data -->
                        <section id="why-we-collect" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Why We Collect Data From You</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                DiasZone collects data from you for the following purposes:
                            </p>
                            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                <li>where it is necessary for the performance of our agreement with you to provide and deliver associated content and Services, including Mobile Legends diamond recharges and related digital goods;</li>
                                <li>where it is necessary for compliance with legal obligations that we may be subjected to under Algerian law and international regulations; and/or</li>
                                <li>where you have given consent to the same.</li>
                            </ol>
                        </section>

                        <!-- Children's Privacy -->
                        <section id="children-privacy" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">We Are Responsible For Children's Privacy</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                Protecting the privacy of young children is especially important. DiasZone does not market to, or knowingly collect personal information from, children under the age of 18 (the legal age in Algeria) on any sites of the company. If we become aware that we have collected personal information from a child under age 18 without verification of parental consent, we take steps to remove that information.
                            </p>
                            <p class="text-gray-700 leading-relaxed">
                                In the event that personal data of a child under age 18 in your care is disclosed to DiasZone, you hereby consent to the processing of the child's personal data and accept and agree to be bound by this Policy on behalf of such child.
                            </p>
                        </section>

                        <!-- When We Collect -->
                        <section id="when-we-collect" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">When Can We Collect Your Personal Information?</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                We may collect personal information directly from you when you:
                            </p>
                            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                                <li>register and/or use our Services or Platform;</li>
                                <li>link your social media or other external accounts to register an account with us;</li>
                                <li>communicate with us (including but not limited to attending to you through our online customer service chats, letters, fax, calls (which may be recorded), face-to-face meetings, social media platforms, emails etc.);</li>
                                <li>register or subscribe for a specific Product and/or Service or our publications (for example, newsletters);</li>
                                <li>participate in any of our surveys;</li>
                                <li>enter into or participate in any competitions, contests or loyalty programs run/organized by DiasZone;</li>
                                <li>register interest and/or request for information of (through our online portals or other available channels) or subscribe to our Products and/or Services;</li>
                                <li>respond to any marketing materials we send out;</li>
                                <li>visit or browse our websites;</li>
                                <li>lodge a complaint with us;</li>
                                <li>provide feedback to us in any way; and/or</li>
                                <li>submit your personal data to us for any reason.</li>
                            </ol>
                            <p class="text-gray-700 leading-relaxed mt-4">
                                Other than personal information obtained from you directly (as detailed above), we may also obtain your personal information from third parties we deal with or are connected with you (credit reference agencies or financial institutions), and from such other sources where you have given your consent for the disclosure of information relating to you, and/or where otherwise lawfully permitted.
                            </p>
                        </section>

                        <!-- How We Collect -->
                        <section id="how-we-collect" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">How Can We Collect Your Personal Information?</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                DiasZone collects information online by asking you for it, for example, when you order a service, apply for a job, or respond to a survey, offer or other promotion. A customer's name, address (postal and email) and telephone number are the most important pieces of information, but we might request other information, based on, for example, the service(s) being ordered or promoted.
                            </p>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                <strong>Cookies:</strong> DiasZone may also use "cookies" and similar technology to obtain information about your visits to our sites or your responses to e-mail from us - both individually and in aggregated data that does not identify you. Such information helps us to improve our sites and to refine our marketing programs and strategies. A cookie is a small text file that is placed on your computer or mobile device when you visit a website. Cookies are widely used to make websites work more efficiently and to provide information to website owners.
                            </p>
                            <p class="text-gray-700 leading-relaxed">
                                You can control cookies through your browser settings and other tools. However, if you choose to block certain cookies, you may not be able to register, login, or access certain parts of our sites, or you may not be able to take advantage of some of our features.
                            </p>
                        </section>

                        <!-- What We Collect -->
                        <section id="what-we-collect" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">What Personal Data Will We Collect?</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                The types of personal data we may collect include, but are not limited to:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li><strong>Identity Data:</strong> Name, username, date of birth, gender, and identification documents;</li>
                                <li><strong>Contact Data:</strong> Email address, telephone number, postal address, and billing address;</li>
                                <li><strong>Financial Data:</strong> Payment card details, bank account information, and transaction history;</li>
                                <li><strong>Transaction Data:</strong> Details about payments and purchases you have made through our Platform, including Mobile Legends diamond purchases;</li>
                                <li><strong>Technical Data:</strong> Internet protocol (IP) address, browser type and version, time zone setting, browser plug-in types and versions, operating system and platform;</li>
                                <li><strong>Profile Data:</strong> Your username and password, purchases or orders made by you, your interests, preferences, feedback and survey responses;</li>
                                <li><strong>Usage Data:</strong> Information about how you use our website and services;</li>
                                <li><strong>Marketing and Communications Data:</strong> Your preferences in receiving marketing from us and our third parties and your communication preferences.</li>
                            </ul>
                        </section>

                        <!-- How We Use -->
                        <section id="how-we-use" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">How Can We Use Your Information</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                We use the information we collect from you for various purposes, including:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li>To provide, maintain, and improve our services, including processing your Mobile Legends diamond recharge orders;</li>
                                <li>To process your transactions and send you related information, including confirmations and invoices;</li>
                                <li>To send you technical notices, updates, security alerts, and support and administrative messages;</li>
                                <li>To respond to your comments, questions, and requests and provide customer service;</li>
                                <li>To communicate with you about products, services, offers, promotions, and events offered by DiasZone and others, and provide news and information we think will be of interest to you;</li>
                                <li>To monitor and analyze trends, usage, and activities in connection with our services;</li>
                                <li>To detect, investigate, and prevent fraudulent transactions and other illegal activities and protect the rights and property of DiasZone and others;</li>
                                <li>To personalize and improve your experience on our Platform;</li>
                                <li>To comply with legal obligations and enforce our terms and conditions.</li>
                            </ul>
                        </section>

                        <!-- Legal Bases -->
                        <section id="legal-bases" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">What Legal Bases Do We Rely On To Process Your Information</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                We process your personal information under the following legal bases:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li><strong>Performance of a contract:</strong> When we process your personal information to fulfill our contractual obligations to you, such as processing your orders and payments;</li>
                                <li><strong>Legitimate interests:</strong> When we process your personal information to pursue our legitimate business interests, such as improving our services, preventing fraud, and ensuring security;</li>
                                <li><strong>Consent:</strong> When you have given us your consent to process your personal information for specific purposes, such as marketing communications;</li>
                                <li><strong>Legal obligation:</strong> When we need to process your personal information to comply with applicable laws and regulations, including Algerian data protection laws.</li>
                            </ul>
                        </section>

                        <!-- Data Storage -->
                        <section id="data-storage" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">How Long We Store Your Data</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                We will only retain your personal data for as long as necessary to fulfill the purposes we collected it for, including for the purposes of satisfying any legal, accounting, or reporting requirements.
                            </p>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                To determine the appropriate retention period for personal data, we consider the amount, nature, and sensitivity of the personal data, the potential risk of harm from unauthorized use or disclosure of your personal data, the purposes for which we process your personal data, and whether we can achieve those purposes through other means, and the applicable legal requirements.
                            </p>
                            <p class="text-gray-700 leading-relaxed">
                                In some circumstances, we may anonymize your personal data (so that it can no longer be associated with you) for research or statistical purposes, in which case we may use this information indefinitely without further notice to you.
                            </p>
                        </section>

                        <!-- Your Rights -->
                        <section id="your-rights" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">What We Request From You & Your Rights</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                We request that you provide accurate, complete, and up-to-date personal information. You have the following rights regarding your personal information:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li><strong>Right to Access:</strong> You have the right to request access to your personal data that we hold about you;</li>
                                <li><strong>Right to Rectification:</strong> You have the right to request correction of inaccurate or incomplete personal data;</li>
                                <li><strong>Right to Erasure:</strong> You have the right to request deletion of your personal data under certain circumstances;</li>
                                <li><strong>Right to Restrict Processing:</strong> You have the right to request restriction of processing of your personal data;</li>
                                <li><strong>Right to Data Portability:</strong> You have the right to receive your personal data in a structured, commonly used format;</li>
                                <li><strong>Right to Object:</strong> You have the right to object to processing of your personal data for certain purposes;</li>
                                <li><strong>Right to Withdraw Consent:</strong> Where we rely on your consent, you have the right to withdraw it at any time.</li>
                            </ul>
                            <p class="text-gray-700 leading-relaxed mt-4">
                                To exercise any of these rights, please contact us using the contact information provided in the "Contact Us" section below.
                            </p>
                        </section>

                        <!-- Privacy & Security -->
                        <section id="privacy-security" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">We Promise Privacy & Security</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li>Encryption of data in transit and at rest;</li>
                                <li>Regular security assessments and updates;</li>
                                <li>Access controls and authentication procedures;</li>
                                <li>Secure payment processing through trusted third-party providers (Flexy, Baridimob, Binance Pay);</li>
                                <li>Employee training on data protection and privacy.</li>
                            </ul>
                            <p class="text-gray-700 leading-relaxed mt-4">
                                However, no method of transmission over the Internet or electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your personal information, we cannot guarantee its absolute security.
                            </p>
                        </section>

                        <!-- Disclosure -->
                        <section id="disclosure" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Disclosure of Your Information (If Needed)</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                We may share your personal information with third parties in the following circumstances:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li><strong>Service Providers:</strong> We may share your information with third-party service providers who perform services on our behalf, such as payment processing, data analysis, email delivery, hosting services, and customer service;</li>
                                <li><strong>Business Partners:</strong> We may share your information with our business partners to offer you certain products, services or promotions;</li>
                                <li><strong>Legal Requirements:</strong> We may disclose your information if required to do so by law or in response to valid requests by public authorities (e.g., a court or a government agency);</li>
                                <li><strong>Business Transfers:</strong> We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing or acquisition of all or a portion of our business to another company;</li>
                                <li><strong>With Your Consent:</strong> We may disclose your personal information for any other purpose with your consent.</li>
                            </ul>
                            <p class="text-gray-700 leading-relaxed mt-4">
                                We may also disclose any of your Personal Information to law enforcement or other appropriate third parties in connection with criminal investigations, investigation of fraud, infringement of intellectual property rights, or other suspected illegal activities, or as otherwise may be required by applicable law, or, as we deem necessary in our sole discretion, in order to protect the legitimate legal interests of DiasZone.
                            </p>
                        </section>

                        <!-- International Transfer -->
                        <section id="international-transfer" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">International Transfer of Data</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                DiasZone operates internationally. You therefore accept and understand that we may store your data in different countries depending on applicable data localisation regulations, or share your Personal Data with recipients (such as Payment Channels, or DiasZone affiliates/subsidiaries) who are located in countries other than Algeria, in order to provide the services to you, process and complete the transactions you wish to make on our Platform, or for any other purposes set out in this Policy.
                            </p>
                            <p class="text-gray-700 leading-relaxed">
                                In such circumstances, we take steps to ensure that overseas recipients shall provide a standard of protection to the Personal Data transferred that is comparable to the protection under applicable data protection laws, including Algerian data protection regulations and international standards.
                            </p>
                        </section>

                        <!-- Withdraw Consent -->
                        <section id="withdraw-consent" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Withdrawing Consent or Requesting Access to Correct Information</h2>
                            
                            <h3 class="text-xl font-bold text-gray-900 mb-3 mt-6">Withdraw Consent</h3>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                To withdraw consent, you may email us at <strong>support@diaszone.com</strong>. You may also withdraw your consent by deleting your account by choosing the menu items My Account -> Edit -> Delete Account. We will process such requests accordingly. You agree that by your withdrawal of consent, you may not be able to continue using our Services in full or have full access to our Platform and we may terminate the contract you have with us. We will liaise with you if we are unable to verify your identity or understand your instructions.
                            </p>
                            
                            <h3 class="text-xl font-bold text-gray-900 mb-3 mt-6">Requesting Access and/or Correcting Personal Data</h3>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                If you have an account with us, you may access your personal data currently in our possession by visiting the <strong>My Account</strong> section of our Platform. Alternatively, you may submit a written request for access or correction of your personal data to our Data Protection Officer at <strong>support@diaszone.com</strong>.
                            </p>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                Please note that we require sufficient information from you in order to process any request for access or correction. This is necessary to verify your identity and ensure the security of your personal data.
                            </p>
                            <p class="text-gray-700 leading-relaxed">
                                Requests for the correction of personal data will be processed within the timeframes prescribed by applicable data protection laws, provided that we are furnished with adequate information to support the requested changes and to verify the accuracy of the corrections.
                            </p>
                        </section>

                        <!-- Policy Changes -->
                        <section id="policy-changes" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Notices to the Changes in Privacy Policies</h2>
                            <p class="text-gray-700 leading-relaxed">
                                DiasZone reserves the right to change this Privacy Policy at any time by notifying registered users via e-mail of the existence of a new Privacy Policy and/or posting the new Privacy Policy on DiasZone. All changes to the Privacy Policy will be effective when posted, and your continued use of DiasZone, product or service after the posting will constitute your acceptance of, and agreement to be bound by, those changes.
                            </p>
                        </section>

                        <!-- Contact -->
                        <section id="contact" class="mt-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Contacting DiasZone</h2>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                Please feel free to contact us if you have any questions about this Privacy Policy, or if you are seeking to exercise any of your statutory rights. We will respond within a reasonable timeframe. You may contact us at:
                            </p>
                            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                <p class="text-gray-700 mb-2"><strong>Email:</strong> <a href="mailto:support@diaszone.com" class="text-purple-600 hover:text-purple-700">support@diaszone.com</a></p>
                                <p class="text-gray-700"><strong>Website:</strong> <a href="https://diaszone.com/" class="text-purple-600 hover:text-purple-700">https://diaszone.com/</a></p>
                            </div>
                        </section>

                        <!-- Footer -->
                        <div class="mt-12 pt-8 border-t border-gray-200 text-center text-gray-600">
                            <p>&copy; {{ date('Y') }} DiasZone. All rights reserved.</p>
                            <p class="mt-2 text-sm">Based in Algeria</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('privacy-sidebar');
    const sidebarContent = sidebar?.querySelector('div');
    const sidebarSpacer = document.getElementById('sidebar-spacer');
    
    if (!sidebar || !sidebarContent) return;
    
    // Get initial values
    const headerOffset = 96; // Header height (64px) + padding (32px)
    let sidebarTop = 0;
    let sidebarWidth = 0;
    let sidebarLeft = 0;
    let isSticky = false;
    
    // Check if we're on desktop (lg breakpoint = 1024px)
    function isDesktop() {
        return window.innerWidth >= 1024;
    }
    
    function initSidebar() {
        if (!isDesktop()) {
            // Reset sticky on mobile
            if (isSticky) {
                isSticky = false;
                sidebar.style.position = '';
                sidebar.style.top = '';
                sidebar.style.left = '';
                sidebar.style.width = '';
                sidebar.style.zIndex = '';
                sidebarContent.style.maxHeight = '';
                sidebarContent.style.overflowY = '';
                if (sidebarSpacer) {
                    sidebarSpacer.style.display = 'none';
                }
            }
            return;
        }
        
        const rect = sidebar.getBoundingClientRect();
        sidebarTop = rect.top + window.pageYOffset;
        sidebarWidth = rect.width;
        sidebarLeft = rect.left;
    }
    
    function updateSidebarPosition() {
        // Only run on desktop
        if (!isDesktop()) {
            if (isSticky) {
                isSticky = false;
                sidebar.style.position = '';
                sidebar.style.top = '';
                sidebar.style.left = '';
                sidebar.style.width = '';
                sidebar.style.zIndex = '';
                sidebarContent.style.maxHeight = '';
                sidebarContent.style.overflowY = '';
                if (sidebarSpacer) {
                    sidebarSpacer.style.display = 'none';
                }
            }
            return;
        }
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Initialize on first scroll
        if (sidebarTop === 0) {
            initSidebar();
        }
        
        // Get the content area and footer
        const contentArea = document.querySelector('.flex-1.min-w-0');
        const footer = document.querySelector('footer');
        
        if (!contentArea) return;
        
        // Calculate when content ends
        const contentRect = contentArea.getBoundingClientRect();
        const contentBottom = contentRect.bottom + scrollTop;
        const viewportHeight = window.innerHeight;
        const footerHeight = footer ? footer.offsetHeight : 0;
        
        // Calculate the maximum scroll position where sidebar should be sticky
        // Sidebar should stop being sticky when content ends and footer starts
        const maxStickyScroll = contentBottom - viewportHeight - footerHeight;
        
        // Check if we should make it sticky
        const shouldBeSticky = scrollTop >= sidebarTop - headerOffset && scrollTop <= maxStickyScroll;
        
        if (shouldBeSticky && !isSticky) {
            isSticky = true;
            sidebar.style.position = 'fixed';
            sidebar.style.top = headerOffset + 'px';
            sidebar.style.left = sidebarLeft + 'px';
            sidebar.style.width = sidebarWidth + 'px';
            sidebar.style.zIndex = '40';
            
            // Show spacer to maintain layout
            if (sidebarSpacer) {
                sidebarSpacer.style.display = 'block';
            }
            
            // Set max height for scrolling
            const availableHeight = window.innerHeight - headerOffset;
            sidebarContent.style.maxHeight = availableHeight + 'px';
            sidebarContent.style.overflowY = 'auto';
        } else if (!shouldBeSticky && isSticky) {
            isSticky = false;
            sidebar.style.position = '';
            sidebar.style.top = '';
            sidebar.style.left = '';
            sidebar.style.width = '';
            sidebar.style.zIndex = '';
            sidebarContent.style.maxHeight = '';
            sidebarContent.style.overflowY = '';
            
            // Hide spacer
            if (sidebarSpacer) {
                sidebarSpacer.style.display = 'none';
            }
        }
        
        // Update height and left position if sticky
        if (isSticky) {
            const availableHeight = window.innerHeight - headerOffset;
            sidebarContent.style.maxHeight = availableHeight + 'px';
            
            // Update left position on resize
            const rect = sidebar.getBoundingClientRect();
            if (rect.left !== sidebarLeft) {
                sidebarLeft = rect.left;
                sidebar.style.left = sidebarLeft + 'px';
            }
        }
    }
    
    // Update on scroll and resize
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(updateSidebarPosition, 10);
    });
    
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            initSidebar();
            updateSidebarPosition();
        }, 100);
    });
    
    // Initial setup
    setTimeout(function() {
        initSidebar();
        updateSidebarPosition();
    }, 100);
});
</script>
@endpush
@endsection

