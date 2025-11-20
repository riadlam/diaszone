@extends('layouts.app')

@section('title', 'Terms of Use - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-7xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4">
                TERMS OF USE
            </h1>
            <p class="text-gray-600">UPDATED AND EFFECTIVE AS OF <strong>{{ date('d.m.Y') }}</strong></p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <!-- Sidebar Navigation - Fixed on Left -->
            <aside id="terms-sidebar" class="w-full lg:w-64 flex-shrink-0 lg:order-1">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Table of Contents</h3>
                    <nav class="space-y-2">
                        <a href="#definitions" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Definitions</a>
                        <a href="#licence" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Licence to Use</a>
                        <a href="#representations" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Representations</a>
                        <a href="#user-id" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">User ID</a>
                        <a href="#services" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Use of Services</a>
                        <a href="#goods" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Goods & Services</a>
                        <a href="#availability" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Availability</a>
                        <a href="#disclaimers" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Disclaimers</a>
                        <a href="#intellectual" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Intellectual Property</a>
                        <a href="#reliability" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Reliability</a>
                        <a href="#account" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Account</a>
                        <a href="#disclosure" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Disclosure</a>
                        <a href="#laws" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Applicable Laws</a>
                        <a href="#suspension" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Suspension</a>
                        <a href="#notices" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Notices</a>
                        <a href="#waiver" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Waiver</a>
                        <a href="#variation" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Variation</a>
                        <a href="#assignment" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Assignment</a>
                        <a href="#binding" class="block text-sm text-gray-700 hover:text-purple-600 hover:bg-purple-50 px-3 py-2 rounded-lg transition-colors">Binding Effect</a>
                    </nav>
                </div>
            </aside>
            
            <!-- Spacer to maintain layout when sidebar becomes sticky -->
            <div id="sidebar-spacer" class="hidden lg:block w-64 flex-shrink-0"></div>

            <!-- Content -->
            <div class="flex-1 min-w-0 lg:order-2">
                <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12 space-y-8">
            <div class="prose prose-lg max-w-none">
                <p class="text-gray-700 leading-relaxed">
                    Welcome to the DiasZone website <strong>https://diaszone.com/</strong> (the "<strong>Site</strong>") and/or DiasZone mobile applications (the "<strong>App</strong>") (collectively, the "<strong>Platform</strong>"). DiasZone is an online digital goods and services platform that facilitates, among others, sale and purchase of Mobile Legends diamonds, game top-ups, and related digital goods between merchants and buyers or customers.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    These Terms of Use govern your rights and obligations, as users of the platform administered and managed by <strong>DiasZone</strong> and its subsidiaries and affiliates (individually and collectively, "<strong>DiasZone</strong>", "<strong>WE</strong>", "<strong>US</strong>" or "<strong>OUR</strong>"). Unless otherwise provided by DiasZone, all new platforms introduced and managed by DiasZone shall be governed by these Terms of Use.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    By registering an account with DiasZone and accessing any of the Platform, you acknowledge and accept that your usage of the Platform (or any of them) shall be governed by these Terms of Use and any other specific rules, procedures, terms and conditions as specified in the respective item's pages, which may be amended by DiasZone at any time or from time-to-time at its absolute discretion. In the event of any inconsistency, the terms and conditions specified in the respective item's pages shall prevail. Your acceptance of these terms of use shall constitute a legally binding agreement between DiasZone and you as the user.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    We may amend, modify or update these Terms of Use from time-to-time. Any change we made to these Terms of Use in the future will be published and posted on the Platform and, where appropriate, notified to you by email, whereupon your continued access to the Platform and/or use of any of the Services shall constitute your acknowledgement, acceptance, and agreement of the changes we make to these Terms of Use. Please check back frequently to see any updates or changes to these Terms of Use.
                </p>

                <!-- Section 1: Definitions -->
                <section id="definitions" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Definitions</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        In these Terms of Use, the following words and expression shall have the following meanings unless the context otherwise requires:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li><strong>Account:</strong> means your account duly registered with DiasZone to facilitate you using the Services available on the Platform (or any of them);</li>
                        <li><strong>Buyer:</strong> means a person who purchases items on the Platform;</li>
                        <li><strong>Content:</strong> means all information, linked pages features, data, text, images, photographs, graphics, music, sounds, video, messages, tags, content, programming, software, tools, application services (including, without limitation, any mobile application services) or other materials made available on or through the Platform or its related services;</li>
                        <li><strong>Intellectual Property:</strong> means all copyrights, trademarks, service marks, brand names, logos, copyrighted information and other intellectual properties belong to the corresponding owners/ publishers/ developers and/or DiasZone, respectively;</li>
                        <li><strong>Item(s):</strong> means any goods, product or service made available for sale on the Platform, including but not limited to Mobile Legends diamonds, game top-ups, and related digital goods;</li>
                        <li><strong>Legal Age:</strong> means the legal age capable of giving consent hereunder pursuant to the applicable laws in your jurisdiction. In Algeria, this is typically 18 years of age;</li>
                        <li><strong>Parties:</strong> means collectively, DiasZone and you and "Party" shall mean any one of them;</li>
                        <li><strong>Platform:</strong> means collectively, the web Platform presently known as DiasZone, DiasZone mobile applications and such other web and/or mobile Platform administered and managed by DiasZone;</li>
                        <li><strong>DiasZone Balance:</strong> means prepaid account owned by the Buyer which can be used solely for the purpose of purchasing items on the Platform;</li>
                        <li><strong>Service(s):</strong> means (a) the Platform; (b) the services provided on the Platform and client software made available through the Platform, including (but not limited to) selling/ reselling/ retailing/ purchasing Mobile Legends diamonds, game top-ups, related merchandise and items, games' credit recharge/ top-up/ reload; and (c) the Content;</li>
                        <li><strong>Submission:</strong> means any material, information or idea you provided to DiasZone by any means;</li>
                        <li><strong>Terms of Use:</strong> means these Terms of Use governing the use of the Services by you as may be amended at any time and from time to time as and when DiasZone shall in its absolute discretion deems necessary and shall include: (i) any rules, procedures, Terms of Use for products, services or facilities, as determined by DiasZone from time to time; and (ii) any documents, directives, correspondence and agreements referred to in these Terms of Use and forming a part hereof, together with any amendments made at any time or from time to time to any of the foregoing; and</li>
                        <li><strong>User ID:</strong> means the unique user identification provided to you during registration of an Account.</li>
                    </ol>
                </section>

                <!-- Section 2: Licence to Use -->
                <section id="licence" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Licence to Use</h2>
                    <p class="text-gray-700 leading-relaxed">
                        In consideration of you agreeing to these Terms of Use and your continuing observance and compliance of these Terms of Use, DiasZone hereby grants you a non-exclusive, non-transferable licence to access the Platform and use the Services upon the terms and subject to the conditions stated herein.
                    </p>
                </section>

                <!-- Section 3: Representations and Warranties -->
                <section id="representations" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Representations and Warranties</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Each time when you access the Platform (or any of them), you irrevocably and unconditionally represent and warrant that:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>you are of Legal Age. Should you be below the Legal Age, you must get permission from a parent or legal guardian to register an Account and that parent or legal guardian must agree and have consented to these Terms of Use on your behalf;</li>
                        <li>your personal information and the documentation submitted in this respect, including, without limitation, your full name, telephone number, correspondence address and email address, are true and accurate. You shall forthwith notify us in writing of any changes in your personal information;</li>
                        <li>you shall keep the password to the Account secure and confidential. You shall not at any time and under any circumstances reveal or disclose your password to the Account to any unauthorized party and shall take all steps to prevent the disclosure of the password to the Account to any unauthorized party;</li>
                    </ol>
                    <p class="text-gray-700 leading-relaxed mt-4 mb-4">
                        You shall not, and agree and undertake to DiasZone not to:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>use the Services to impersonate any person or entity, or otherwise misrepresent your affiliation with a person or entity;</li>
                        <li>directly or indirectly, use the Services for any commercial purposes, save as otherwise permitted by DiasZone;</li>
                        <li>use the Platform or the Services (or any of them) to conduct any fraudulent, immoral or illegal activities or such activities that may infringe the intellectual property rights of third parties or obtain any advantage, benefit or secret profit from any third party;</li>
                        <li>use any Intellectual Property belonging to DiasZone or any other third-party proprietors listed on the Platform, including, without limitation, trademarks or trade names, whether registered or not, without the prior written consent of DiasZone;</li>
                        <li>take any action that may undermine or manipulate the feedback or ratings system;</li>
                        <li>be disruptive, be offensive or be a nuisance in any manner whatsoever to other users of the Platform or the employees of DiasZone;</li>
                        <li>attempt to decompile, reverse engineer, disassemble or hack the Services (or any portion thereof), or to defeat or overcome any encryption technology or security measures implemented by us with respect to the Services and/or data transmitted, processed or stored by us;</li>
                        <li>harvest or collect any information about or regarding other Account holders, including, without limitation, any personal or business information;</li>
                        <li>upload, email, post, transmit or otherwise make available any unsolicited or unauthorized advertising, promotional materials, 'junk mails', 'spam', 'chain letters', 'pyramid schemes' or any other unauthorized form of solicitation;</li>
                        <li>upload, email, post, transmit or otherwise make available any material that contains software viruses, worms, Trojan-horses or any other computer code, routines, files or programs designed to directly or indirectly interfere with, manipulate, interrupt, destroy or limit the functionality or integrity of any computer software or hardware or data or telecommunications equipment;</li>
                        <li>interfere with, manipulate or disrupt the Services or servers or networks connected to the Services or any other use and enjoyment of the Services, or disobey any requirements, procedures, policies or regulations of networks connected to the Platform;</li>
                        <li>take any action or engage in any conduct that could directly or indirectly damage, disable, overburden, or impair the Services or the servers or network connected to the Services;</li>
                        <li>use the Services in any manner that could damage, disable, overburden, or impair any DiasZone server, or the network(s) connected to any DiasZone server, or interfere with any other party's use and enjoyment of the Platform;</li>
                        <li>gain unauthorized access to any portion of the Platform, any other Accounts, computer systems or networks connected to any DiasZone server, through hacking, password mining or any other means;</li>
                        <li>obtain or attempt to obtain any materials or information through any means not intentionally made available through the Platform;</li>
                        <li>use the Services to violate any applicable local, state, national or international law, statute, ordinance, rule, regulation or ethical code;</li>
                        <li>use the Services in any way that violates these Terms of Use.</li>
                    </ol>
                </section>

                <!-- Section 4: User ID -->
                <section id="user-id" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. User ID</h2>
                    <p class="text-gray-700 leading-relaxed">
                        You are responsible for maintaining the confidentiality of your User ID and password. You agree to notify DiasZone immediately of any unauthorized use of your Account or any other breach of security. DiasZone will not be liable for any loss that you may incur as a result of someone else using your password or Account, either with or without your knowledge. However, you could be held liable for losses incurred by DiasZone or another party due to someone else using your Account or password.
                    </p>
                </section>

                <!-- Section 5: Use of Services -->
                <section id="services" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Use of Services</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        The Services are provided for your personal, non-commercial use only. You may not use the Services for any commercial purpose without the express written consent of DiasZone. You agree not to reproduce, duplicate, copy, sell, trade, resell or exploit for any commercial purposes, any portion of the Services, use of the Services, or access to the Services.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        DiasZone reserves the right to modify, suspend, or discontinue the Services (or any part thereof) at any time with or without notice to you, and DiasZone will not be liable to you or to any third party should it exercise such rights.
                    </p>
                </section>

                <!-- Section 6: Goods & Services -->
                <section id="goods" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Goods & Services</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        DiasZone facilitates the sale and purchase of Mobile Legends diamonds and related digital goods. The following payment methods are available:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-4">
                        <li><strong>Flexy:</strong> Mobile payment method where you upload a receipt to complete your recharge</li>
                        <li><strong>Baridimob:</strong> Mobile payment solution (coming soon)</li>
                        <li><strong>Cryptocurrency:</strong> Payment via Binance Pay using cryptocurrency</li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed">
                        All prices are displayed in the currency specified on the Platform. DiasZone reserves the right to change prices at any time without prior notice. Once a purchase is completed, the price paid will not be adjusted for any subsequent price change.
                    </p>
                </section>

                <!-- Section 7: Availability of Services -->
                <section id="availability" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Availability of Services</h2>
                    <p class="text-gray-700 leading-relaxed">
                        While DiasZone will use reasonable efforts to make the Services available, DiasZone does not guarantee that the Services will be available at all times. The Services may be unavailable due to maintenance, updates, or circumstances beyond DiasZone's control. DiasZone shall not be liable for any loss or damage arising from the unavailability of the Services.
                    </p>
                </section>

                <!-- Section 8: Disclaimers, Exclusions and Force Majeure -->
                <section id="disclaimers" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Disclaimers, Exclusions and Force Majeure</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        THE SERVICES ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        DiasZone shall not be liable for any failure or delay in performance under these Terms of Use which is due to earthquake, fire, flood, act of God, act of war, terrorism, epidemic, pandemic, government action, or other causes which are beyond DiasZone's reasonable control.
                    </p>
                </section>

                <!-- Section 9: Intellectual Property Rights -->
                <section id="intellectual" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Intellectual Property Rights</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        All content, features, and functionality of the Platform, including but not limited to text, graphics, logos, icons, images, audio clips, digital downloads, data compilations, and software, is the property of DiasZone or its content suppliers and is protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        Mobile Legends and related trademarks are the property of their respective owners. DiasZone is not affiliated with, endorsed by, or sponsored by Mobile Legends or its developers.
                    </p>
                </section>

                <!-- Section 10: Reliability of Platform -->
                <section id="reliability" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Reliability of Platform</h2>
                    <p class="text-gray-700 leading-relaxed">
                        While DiasZone strives to provide a reliable and secure platform, you acknowledge that the internet is an inherently unstable medium, and errors, omissions, interruptions, and delays may occur in the operation of the Platform at any time. DiasZone does not guarantee that the Platform will be error-free or that defects will be corrected.
                    </p>
                </section>

                <!-- Section 11: Account -->
                <section id="account" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Account</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        To use certain features of the Services, you must register for an Account. You agree to:
                    </p>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>provide accurate, current, and complete information during registration;</li>
                        <li>maintain and promptly update your Account information;</li>
                        <li>maintain the security of your password and identification;</li>
                        <li>accept all responsibility for activities that occur under your Account;</li>
                        <li>notify DiasZone immediately of any unauthorized use of your Account.</li>
                    </ol>
                </section>

                <!-- Section 12: Disclosure of Information -->
                <section id="disclosure" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">12. Disclosure of Information</h2>
                    <p class="text-gray-700 leading-relaxed">
                        DiasZone may disclose your personal information if required to do so by law or in the good faith belief that such disclosure is reasonably necessary to: (a) comply with legal process; (b) enforce these Terms of Use; (c) respond to claims that any content violates the rights of third parties; or (d) protect the rights, property, or personal safety of DiasZone, its users, and the public.
                    </p>
                </section>

                <!-- Section 13: Applicable Laws and Indemnity -->
                <section id="laws" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">13. Applicable Laws and Indemnity</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        These Terms of Use shall be governed by and construed in accordance with the laws of Algeria, without regard to its conflict of law provisions. You agree to submit to the exclusive jurisdiction of the courts of Algeria.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        You agree to indemnify, defend and hold harmless DiasZone, and its shareholders, subsidiaries, affiliates, related entities, directors, officers, agents, representatives, co-branders and employees (collectively, the 'Indemnified Parties') from and against any and all claims, actions, proceedings and suits and all related liabilities, damages, settlements, penalties, fines, costs and expenses (including, without limitation, the legal costs and dispute resolution expenses) incurred by any Indemnified Party arising out of or relating to: (i) your violation or breach of any of these Terms of Use or any policy or guideline referenced herein; (ii) your use or misuse of the Platform or Services, or (iii) your breach of any laws or any rights of a third party.
                    </p>
                </section>

                <!-- Section 14: Suspension, Termination, Cancellation of Services -->
                <section id="suspension" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">14. Suspension, Termination, Cancellation of Services</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        The Services (or any part thereof) may be cancelled by DiasZone at any time without prior notice to you. After cancellation, the Services (or any part thereof) may be reinstated in such manner and on such Terms of Use as DiasZone may at its absolute discretion determine.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        You may deactivate your Account at any time by notifying us of your desire to do so, subject always to any applicable closure fees.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        DiasZone reserves the right at all times to suspend or block access to and use of the Services (or any part thereof) for any reason whatsoever and for any length of time and upon any conditions that DiasZone may at its absolute discretion determine. Grounds for suspension or termination may include, but not limited to:
                    </p>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700">
                        <li>the Account has been inactive for a consecutive period of 36 months or any other period as determined by DiasZone;</li>
                        <li>having multiple user accounts or allowing unauthorized persons to access and use the Account;</li>
                        <li>in our opinion, there is dishonesty, suspected fraud, illegality, criminality or misrepresentation in the conduct of your Account or your use of the Platform and/or Services;</li>
                        <li>you are in breach or we have reasonable grounds to believe that you have breached any of these Terms of Use and/or any applicable terms and conditions as may be provided by DiasZone from time-to-time;</li>
                        <li>you are in breach of any acts, statute, laws, by-laws, rules, regulations, guidelines and/or policies by any authority, regulatory body or government agency;</li>
                        <li>you have acted in bad faith or with malicious intent, or that we have reasonable grounds to believe that your behaviour is harmful, of defamatory nature or abusive to other users, third parties and/or DiasZone;</li>
                        <li>if we are required to do so pursuant to an order of a court or by any governmental or regulatory authority having the relevant jurisdiction;</li>
                        <li>you have submitted false documents or have declared false information during your registration with or application to DiasZone; and/or</li>
                        <li>you fail to provide any additional information which we may request from you from time-to-time for verification purposes.</li>
                    </ol>
                    <p class="text-gray-700 leading-relaxed mt-4">
                        Use of the Platform, Services and/or Account for suspicious, fraudulent, illegal, harassing, defamatory, threatening or abusive purposes may be referred by us to the relevant law enforcement authorities without notice to you.
                    </p>
                </section>

                <!-- Section 15: Notices -->
                <section id="notices" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">15. Notices</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        All notices, demands, requests or other communications to be given or made under these Terms of Use shall be in writing, and shall be sufficiently given or made to the other party by serving such notice at or sending such notice by hand, registered post or electronic mail to the contact details as notified by one party to the other from time-to-time or via the communication channel made available on the Platform.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        Notice shall be deemed given: (a) in the case of hand delivery, upon the receipt of written acknowledgment signed by the recipient; (b) in the case of registered post, five (5) business days after posting; and (c) in the case of email or the communication channel available on the Platform, on the day of transmission provided that the sender has not received a failed or undeliverable message from the host provider of the recipient within the day of transmission.
                    </p>
                </section>

                <!-- Section 16: Waiver And Severance -->
                <section id="waiver" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">16. Waiver And Severance</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Any failure by DiasZone to enforce at any time or for any period any one or more of these Terms of Use shall not be a waiver of them or of the right at any time subsequently to enforce these Terms of Use.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        In the event that any provisions of these Terms of Use is declared by any judicial or other competent authority to be void, voidable, illegal or otherwise unenforceable, DiasZone shall amend that provision in such reasonable manner as would achieve the intention of DiasZone or at the discretion of DiasZone it may be severed from these Terms of Use and the remaining provisions remain in full force and effect.
                    </p>
                </section>

                <!-- Section 17: Variation -->
                <section id="variation" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">17. Variation</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        These Terms of Use may be modified, added to, deleted or varied by DiasZone by way of posting on the Platform or in any such other manner as DiasZone may in its absolute discretion determine.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        You agree that continued use of the Services shall constitute your acceptance of these Terms of Use (as modified and varied from time to time).
                    </p>
                </section>

                <!-- Section 18: Assignment -->
                <section id="assignment" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">18. Assignment</h2>
                    <p class="text-gray-700 leading-relaxed">
                        You may not assign your rights under these Terms of Use without the prior written consent of DiasZone.
                    </p>
                </section>

                <!-- Section 19: Binding Effect -->
                <section id="binding" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">19. Binding Effect</h2>
                    <p class="text-gray-700 leading-relaxed">
                        These Terms of Use shall be binding on your heirs, personal and legal representatives, estate, successors-in-title and permitted assigns (where applicable).
                    </p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('terms-sidebar');
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

