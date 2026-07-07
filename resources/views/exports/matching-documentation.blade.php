<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SmarTern - Matching Algorithm Documentation</title>
    <style>
        body, h1, h2, h3, h4, h5, h6, .logo, .title, table, th, td, ul, li, div, p, span {
            font-family: Helvetica, Arial, sans-serif !important;
        }
        body {
            color: # ;
            line-height: 1.8;
            margin: 40px;
            font-size: 16px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid # Bfivesixninefour;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        .logo {
            font-family: Helvetica, Arial, sans-serif !important;
            font-size: 28px;
            font-weight: bold !important;
            color: # Bfivesixninefour;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .title {
            font-size: 18px;
            color: # ;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        h2 {
            font-size: 18px;
            color: # Bfivesixninefour;
            text-transform: uppercase;
            border-left: 4px solid # Bfivesixninefour;
            padding-left: 15px;
            margin-top: 40px;
            margin-bottom: 20px;
        }
        p {
            text-align: justify;
            margin-bottom: 15px;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .highlight {
            background: # fzerofsevenff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid # doneenineff;
            margin-bottom: 20px;
        }
        .highlight p {
            text-align: center;
            margin-bottom: 0;
            font-weight: bold;
            color: # Bfivesixninefour;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid # eee;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: # feightfafc;
            color: # Bfivesixninefour;
            font-size: 12px;
            text-transform: uppercase;
        }
        .reference-box {
            background: # fafafa;
            border: 1px solid # eee;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .reference-box h4 {
            color: # ;
            margin-top: 0;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            border-bottom: 1px solid # eee;
            padding-bottom: 10px;
        }
        .ref-item {
            margin-bottom: 18px;
            font-size: 12px;
            color: # ;
        }
        .ref-link {
            color: # Bfivesixninefour;
            text-decoration: underline;
            display: block;
            margin-top: 4px;
            font-size: 10px;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 10px;
            color: # ;
            border-top: 1px solid # eee;
            padding-top: 20px;
        }
        .weight-box {
            display: inline-block;
            width: 80px;
            font-weight: bold;
            color: # Bfivesixninefour;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SmarTern</div>
        <div class="title">Technical Overview: Intern-Office Matching Framework</div>
    </div>

    <div class="section">
        <p>This document provides the administrative and technical foundation for the SmarTern "Smart Matching" algorithm. The framework utilizes a multi-criteria decision model to ensure mathematically objective placements by integrating quantitative skill metrics with qualitative behavioral evaluations.</p>
    </div>

    <div class="section">
        <h2>1. The Core Scoring Methodology</h2>
        <p>The placement engine calculates compatibility through a weighted aggregation of an intern's proven capabilities and their professional assessment. This dual-focus approach ensures that technical proficiency is balanced by institutional suitability.</p>
        <div class="highlight">
            <p>Final Score = (Skill Alignment × 80%) + (Behavioral Score × 20%)</p>
        </div>
        <ul>
            <li><strong>Skill Alignment (80%):</strong> An analysis of technical and soft skills mapped against priority-weighted office requirements.</li>
            <li><strong>Behavioral Score (20%):</strong> Qualitative performance data captured during professional interviews.</li>
        </ul>
    </div>

    <div class="section">
        <h2>2. Data Integrity & Verification</h2>
        <p>The system applies a "Triangle of Verification" to determine the reliability of skill data. Multipliers are applied based on the source of information, rewarding applicants whose skills have been externally validated.</p>
        <table>
            <thead>
                <tr>
                    <th>Data Source</th>
                    <th>Weight Multiplier</th>
                    <th>Confidence Level Standard</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Supervisor Verified</strong></td>
                    <td><span class="weight-box">1.5x</span></td>
                    <td>Highest Integrity: Explicitly confirmed during professional assessment.</td>
                </tr>
                <tr>
                    <td><strong>Semantic Resume Match</strong></td>
                    <td><span class="weight-box">1.2x</span></td>
                    <td>Validated: Identified via automated parsing of primary professional documents.</td>
                </tr>
                <tr>
                    <td><strong>Self-Reported Data</strong></td>
                    <td><span class="weight-box">1.0x</span></td>
                    <td>Baseline: Information provided by the intern during the initial onboarding process.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>3. Alignment Tiers</h2>
        <p>Thresholds for placement recommendations follow established industrial-organizational competency benchmarks to guide immediate administrative action.</p>
        <table>
            <thead>
                <tr>
                    <th>Alignment Tier</th>
                    <th>Percentage Range</th>
                    <th>Administrative Recommendation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: #10b981;">Strong Alignment</strong></td>
                    <td>80% - 100%</td>
                    <td>Exceptional candidate; demonstrates strong synergy with office priorities.</td>
                </tr>
                <tr>
                    <td><strong style="color: #3b82f6;">Good Alignment</strong></td>
                    <td>60% - 79%</td>
                    <td>Qualified candidate; possesses essential core competencies.</td>
                </tr>
                <tr>
                    <td><strong style="color: #6366f1;">Fair Alignment</strong></td>
                    <td>40% - 59%</td>
                    <td>Capable candidate; may benefit from targeted departmental guidance.</td>
                </tr>
                <tr>
                    <td><strong style="color: #f59e0b;">Limited Alignment</strong></td>
                    <td>Below 40%</td>
                    <td>Low structural alignment with specific office requirements.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>4. Standards & Academic References</h2>
        <div class="reference-box">
            <h4>Methodology Compliance & Industry Standards:</h4>
            
            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                Afshari, A. R., Nikolic, M., &amp; Akilic, D. Z. (2014). Applications of fuzzy decision making for personnel selection problem: A review. <em>Journal of Engineering Management and Competitiveness</em>, 4(2), 68&ndash;77. <a href="https://doi.org/10.5937/jemc1402068A" style="color: #0B5694; text-decoration: underline;">https://doi.org/10.5937/jemc1402068A</a>
            </div>

            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                Al-Azawei, A., Al-Azawei, S., &amp; Al-Rshaidat, M. (2026). Explainable fuzzy multi-criteria decision making with rule extraction. <em>Applied Sciences</em>, 16(10), 5169. <a href="https://doi.org/10.3390/app16105169" style="color: #0B5694; text-decoration: underline;">https://doi.org/10.3390/app16105169</a>
            </div>

            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                Alguliyev, R. M., Aliguliyev, R. M., &amp; Mahmudova, R. S. (2015). Multicriteria selection of cloud computing services. <em>The Scientific World Journal</em>, 2015, Article 612767. <a href="https://doi.org/10.1155/2015/612767" style="color: #0B5694; text-decoration: underline;">https://doi.org/10.1155/2015/612767</a>
            </div>

            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                Bogd&aacute;ny, E., Cserh&aacute;ti, G., &amp; Csizmadia, T. (2025). Competency-based curriculum design: A Q-methodology study. <em>Cogent Education</em>, 12(1), Article 2448885. <a href="https://doi.org/10.1080/2331186x.2024.2448885" style="color: #0B5694; text-decoration: underline;">https://doi.org/10.1080/2331186x.2024.2448885</a>
            </div>

            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                Hwang, C.-L., &amp; Yoon, K. (1981). <em>Multiple attribute decision making: Methods and applications</em>. Springer-Verlag. <a href="https://doi.org/10.1007/978-3-642-48318-9" style="color: #0B5694; text-decoration: underline;">https://doi.org/10.1007/978-3-642-48318-9</a>
            </div>

            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                Madanchian, M., &amp; Taherdoost, H. (2023). A comprehensive guide to the TOPSIS method for multi-criteria decision making. <em>Sustainable Social Development</em>, 1(1), 2220. <a href="https://doi.org/10.54517/ssd.v1i1.2220" style="color: #0B5694; text-decoration: underline;">https://doi.org/10.54517/ssd.v1i1.2220</a> (Open Access)
            </div>

            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                Roszkowska, E. (2011). Multi-criteria decision making models by applying the TOPSIS method to crisp and interval data. <em>Multiple Criteria Decision Making</em>, 6, 200&ndash;230. <a href="https://mcdm.ue.katowice.pl/files/papers/mcdm11(6)_15.pdf" style="color: #0B5694; text-decoration: underline;">https://mcdm.ue.katowice.pl/files/papers/mcdm11(6)_15.pdf</a> (Open Access)
            </div>

            <div class="ref-item" style="margin-bottom: 15px; padding-left: 30px; text-indent: -30px; line-height: 1.5; font-size: 13px;">
                International Organization for Standardization. (2020). <em>Assessment service delivery &mdash; Procedures and methods to assess people in work and organizational settings &mdash; Part 2: Requirements for service providers</em> (ISO Standard No. 10667-2:2020). <a href="https://www.iso.org/standard/74717.html" style="color: #0B5694; text-decoration: underline;">https://www.iso.org/standard/74717.html</a>
            </div>
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} SmarTern Internship Management System &bull; Confidential Administrative Documentation
    </div>
</body>
</html>
