<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - ifiti Real Estate</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .terms-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .terms-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .terms-header h1 {
            font-size: 2.5rem;
            background: linear-gradient(135deg,rgb(39, 105, 50) 0%,rgb(68, 139, 92) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .last-updated {
            color: #888;
            font-size: 0.9rem;
            font-style: italic;
        }

        .terms-section {
            margin-bottom: 2.5rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            border-left: 4px solidrgb(30, 116, 73);
        }

        .terms-section h2 {
            color:rgb(102, 234, 124);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .terms-section h3 {
            color: #888;
            font-size: 1.2rem;
            margin: 1.5rem 0 0.8rem 0;
        }

        .terms-section p {
            line-height: 1.7;
            margin-bottom: 1rem;
            color: #ccc;
        }

        .terms-section ul, .terms-section ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }

        .terms-section li {
            margin-bottom: 0.5rem;
            color: #ccc;
            line-height: 1.6;
        }

        .highlight-box {
            background: linear-gradient(135deg, rgba(103, 145, 95, 0.1) 0%, rgba(53, 117, 63, 0.1) 100%);
            border: 1px solid rgba(0, 255, 13, 0.3);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
        }

        .contact-info h3 {
            color:rgb(102, 234, 102);
            margin-bottom: 1rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg,rgb(124, 234, 102) 0%,rgb(75, 162, 101) 100%);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 234, 142, 0.3);
        }

        .section-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg,rgb(139, 234, 102) 0%,rgb(89, 162, 75) 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .terms-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .terms-header h1 {
                font-size: 2rem;
            }

            .terms-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">
            ← Back to Home
        </a>

        <div class="terms-container">
            <div class="terms-header">
                <h1>Terms and Conditions</h1>
                <p class="last-updated">Last Updated: July 2025</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">1</span> Introduction and Acceptance</h2>
                <p>Welcome to ifiti Real Estate Platform ("ifiti", "we", "us", or "our"). These Terms and Conditions ("Terms") govern your use of our real estate platform, website, and related services.</p>
                
                <div class="highlight-box">
                    <p><strong>By accessing or using ifiti, you agree to be bound by these Terms.</strong> If you do not agree to these Terms, please do not use our platform.</p>
                </div>

                <p>ifiti is a comprehensive real estate platform that connects real estate agents with potential buyers, and renters. Our platform allows agents to showcase properties, and build their professional presence in the real estate industry.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">2</span> Definitions</h2>
                <ul>
                    <li><strong>"Platform"</strong> refers to the ifiti website, mobile applications, and all related services</li>
                    <li><strong>"Agent"</strong> refers to real estate professionals who create accounts on our platform</li>
                    <li><strong>"User"</strong> refers to any person who accesses or uses our platform</li>
                    <li><strong>"Content"</strong> refers to all text, images, videos, data, and other materials posted on the platform</li>
                    <li><strong>"Posts"</strong> refers to real estate advertisements posted by agents</li>
                    <li><strong>"Services"</strong> refers to all features and functionalities provided by ifiti</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">3</span> Eligibility and Account Registration</h2>
                
                <h3>3.1 Agent Eligibility</h3>
                <p>To create an agent account on ifiti, you must:</p>
                <ul>
                    <li>Be at least 18 years of age</li>
                    <li>Provide accurate and complete registration information</li>
                    <li>Maintain the confidentiality of your account credentials</li>
                    <li>Accept full responsibility for all activities under your account</li>
                </ul>

                <h3>3.2 Account Verification</h3>
                <p>We reserve the right to verify your professional credentials. Failure to provide valid documentation may result in account suspension or termination.</p>

                
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">4</span> Platform Usage and Content</h2>
                
                <h3>4.1 Permitted Use</h3>
                <p>You may use ifiti for legitimate real estate business purposes, including:</p>
                <ul>
                    <li>Creating and managing property posts</li>
                    <li>Connecting with potential clients</li>
                </ul>

                <h3>4.2 Content Guidelines</h3>
                <p>All content posted on ifiti must:</p>
                <ul>
                    <li>Be accurate and truthful</li>
                    <li>Respect intellectual property rights</li>
                    <li>Be appropriate and professional</li>
                    <li>Not contain misleading or false information</li>
                </ul>

                <h3>4.3 Automatic Content Deletion</h3>
                <div class="highlight-box">
                    <p><strong>Important:</strong> All posts are automatically deleted after 7 days to ensure content freshness and relevance.</p>
                </div>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">5</span> Prohibited Activities</h2>
                <p>You agree not to:</p>
                <ol>
                    <li>Post false, misleading, or discriminatory content</li>
                    <li>Violate any local, state, or federal laws</li>
                    <li>Infringe on intellectual property rights</li>
                    <li>Engage in spam or unsolicited communications</li>
                    <li>Attempt to hack, disrupt, or damage the platform</li>
                    <li>Create multiple accounts or impersonate others</li>
                    <li>Use automated tools to access the platform</li>
                    <li>Share inappropriate or offensive content</li>
                    <li>Use the platform for any illegal activities</li>
                </ol>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">6</span> Intellectual Property Rights</h2>
                
                <h3>6.1 Platform Ownership</h3>
                <p>ifiti owns all rights, title, and interest in the platform, including all software, designs, trademarks, and proprietary technology.</p>

                <h3>6.2 User Content</h3>
                <p>You retain ownership of content you post, but grant ifiti a non-exclusive, worldwide, royalty-free license to use, display, and distribute your content on the platform.</p>

                <h3>6.3 Respect for Others' Rights</h3>
                <p>You must respect the intellectual property rights of others and only post content you own or have permission to use.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">7</span> Privacy and Data Protection</h2>
                <p>Your privacy is important to us. Our data practices are governed by our Privacy Policy, which is incorporated into these Terms by reference.</p>
                
                <h3>7.1 Data Collection</h3>
                <p>We collect information necessary to provide our services, including:</p>
                <ul>
                    <li>Account registration information</li>
                    <li>Professional credentials and information</li>
                    <li>Content and communications on the platform</li>
                    <li>Usage data and analytics</li>
                </ul>

                <h3>7.2 Data Security</h3>
                <p>We implement appropriate security measures to protect your personal information, but cannot guarantee absolute security.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">8</span> Platform Availability and Modifications</h2>
                
                <h3>8.1 Service Availability</h3>
                <p>We strive to maintain platform availability but do not guarantee uninterrupted service. We may perform maintenance, updates, or modifications that temporarily affect availability.</p>

                <h3>8.2 Platform Changes</h3>
                <p>We reserve the right to modify, update, or discontinue features of the platform at any time with or without notice.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">9</span> Disclaimers and Limitations</h2>
                
                <div class="highlight-box">
                    <h3>9.1 Platform Disclaimer</h3>
                    <p><strong>ifiti is provided "as is" without warranties of any kind.</strong> We do not guarantee the accuracy, completeness, or reliability of any content on the platform.</p>
                </div>

                <h3>9.2 Real Estate Transactions</h3>
                <p>ifiti is a marketing platform only. We do not:</p>
                <ul>
                    <li>Provide real estate brokerage services</li>
                    <li>Guarantee the accuracy of property information</li>
                    <li>Participate in real estate transactions</li>
                    <li>Provide legal or financial advice</li>
                </ul>

                <h3>9.3 Third-Party Content</h3>
                <p>We are not responsible for content posted by users or third parties. Users are solely responsible for their own content and interactions.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">10</span> Limitation of Liability</h2>
                <p>To the maximum extent permitted by law, ifiti shall not be liable for:</p>
                <ul>
                    <li>Indirect, incidental, or consequential damages</li>
                    <li>Loss of profits, data, or business opportunities</li>
                    <li>Damages arising from platform downtime or technical issues</li>
                    <li>Actions or omissions of other users</li>
                    <li>Real estate transaction disputes</li>
                </ul>
                
                <div class="highlight-box">
                    <p>Our total liability shall not exceed $100 or the amount you paid for services in the past 12 months, whichever is greater.</p>
                </div>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">11</span> Account Termination</h2>
                
                <h3>11.1 Termination by You</h3>
                <p>You may terminate your account at any time by contacting us.</p>

                <h3>11.2 Termination by Us</h3>
                <p>We may suspend or terminate your account if you:</p>
                <ul>
                    <li>Violate these Terms</li>
                    <li>Engage in prohibited activities</li>
                    <li>Provide false information</li>
                    <li>Remain inactive for extended periods</li>
                </ul>

                <h3>11.3 Effect of Termination</h3>
                <p>Upon termination, your access to the platform will cease, and your content may be deleted according to our data retention policies.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">12</span> Dispute Resolution</h2>
                
                <h3>12.1 Governing Law</h3>
                <p>These Terms are governed by the laws of Nigeria, without regard to conflict of law principles.</p>

                <h3>12.2 Dispute Resolution Process</h3>
                <p>Before pursuing legal action, parties agree to attempt resolution through:</p>
                <ol>
                    <li>Direct negotiation</li>
                    <li>Mediation by a neutral third party</li>
                    <li>Binding arbitration if mediation fails</li>
                </ol>

                <h3>12.3 Class Action Waiver</h3>
                <p>You agree to resolve disputes individually and waive the right to participate in class action lawsuits.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">13</span> General Provisions</h2>
                
                <h3>13.1 Entire Agreement</h3>
                <p>These Terms, along with our Privacy Policy, constitute the entire agreement between you and ifiti.</p>

                <h3>13.2 Severability</h3>
                <p>If any provision of these Terms is found unenforceable, the remaining provisions will continue in full force.</p>

                <h3>13.3 Assignment</h3>
                <p>You may not assign your rights under these Terms. We may assign our rights and obligations to any party.</p>

                <h3>13.4 Updates to Terms</h3>
                <p>We may update these Terms periodically. Continued use of the platform after changes constitutes acceptance of new Terms.</p>
            </div>

            <div class="terms-section">
                <h2><span class="section-icon">14</span> Contact Information</h2>
                <div class="contact-info">
                    <h3>Questions About These Terms?</h3>
                    <p>If you have questions about these Terms and Conditions, please contact us:</p>
                    <p><strong>Email:</strong> ifitikeyz@gmail.com</p>
                    <p><strong>Phone:</strong> 08080089096 </p>
                    <p><strong>Business Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM</p>
                </div>
            </div>

            <div class="highlight-box" style="text-align: center; margin-top: 3rem;">
                <p><strong>Thank you for choosing ifiti!</strong></p>
                <p>We're committed to providing a professional, secure, and effective platform for real estate professionals.</p>
            </div>
        </div>
    </div>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add reading progress indicator
        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            
            if (!document.querySelector('.progress-bar')) {
                const progressBar = document.createElement('div');
                progressBar.className = 'progress-bar';
                progressBar.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: ${scrolled}%;
                    height: 3px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    z-index: 1000;
                    transition: width 0.1s ease;
                `;
                document.body.appendChild(progressBar);
            } else {
                document.querySelector('.progress-bar').style.width = scrolled + '%';
            }
        });
    </script>
</body>
</html>
