<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SmarTern - Security Anomaly Detection Documentation</title>
    <style>
        body, h1, h2, h3, h4, h5, h6, .logo, .title, table, th, td, ul, li, div, p, span {
            font-family: Helvetica, Arial, sans-serif !important;
        }
        body {
            color: #333;
            line-height: 1.8;
            margin: 40px;
            font-size: 16px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0B5694;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        .logo {
            font-family: Helvetica, Arial, sans-serif !important;
            font-size: 28px;
            font-weight: bold !important;
            color: #0B5694;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .title {
            font-size: 18px;
            color: #666;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        h2 {
            font-size: 18px;
            color: #0B5694;
            text-transform: uppercase;
            border-left: 4px solid #0B5694;
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
            background: #f0f7ff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #d1e9ff;
            margin-bottom: 20px;
        }
        .highlight p {
            text-align: center;
            margin-bottom: 0;
            font-weight: bold;
            color: #0B5694;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #eee;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            color: #0B5694;
            font-size: 12px;
            text-transform: uppercase;
        }
        .reference-box {
            background: #fafafa;
            border: 1px solid #eee;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .reference-box h4 {
            color: #555;
            margin-top: 0;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .ref-item {
            margin-bottom: 18px;
            font-size: 12px;
            color: #666;
        }
        .ref-link {
            color: #0B5694;
            text-decoration: underline;
            display: block;
            margin-top: 4px;
            font-size: 10px;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .weight-box {
            display: inline-block;
            width: 80px;
            font-weight: bold;
            color: #0B5694;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SmarTern</div>
        <div class="title">Technical Specification: ML Anomaly Detection Engine</div>
    </div>

    <div class="section">
        <p>This specification outlines the architectural and mathematical foundations of the SmarTern <strong>Unsupervised Outlier Detection</strong> system. Utilizing an Isolation Forest topology, the engine performs real-time behavioral analysis to identify systemic deviations without requiring pre-labeled threat signatures.</p>
    </div>

    <div class="section">
        <h2>1. The Isolation Forest Methodology</h2>
        <p>The engine operates on the axiom that anomalies are "few and different." Unlike traditional proximity-based models, our system employs <strong>Stochastic Partitioning</strong>. By recursively splitting the data space, anomalies are isolated near the root of the decision trees due to their structural distinctness, while normal points remain deep within the branches.</p>
        <div class="highlight">
            <p>Heuristic Anomaly Score (s) is in the range [0, 1]</p>
        </div>
        <ul>
            <li><strong>Score approaches 1.0 (Critical):</strong> Rapid isolation achieved; indicates a statistically significant deviation from established norms.</li>
            <li><strong>Score approaches 0.5 (Normal):</strong> Deep tree traversal required; indicates behavior consistent with the average data density.</li>
            <li><strong>Score approaches 0.0 (High Density):</strong> Maximum path length; indicates highly repetitive, standard system interactions.</li>
        </ul>
    </div>

    <div class="section">
        <h2>2. Feature Vectorization Dimensions</h2>
        <p>Before analysis, raw audit logs are transformed into a normalized <strong>Feature Vector</strong>. This process, known as <strong>Feature Engineering</strong>, quantifies qualitative logs into mathematical coordinates bounded between 0.0 and 1.0 that the ML model can interpret.</p>
        <table>
            <thead>
                <tr>
                    <th>Vector Dimension</th>
                    <th>Technical Objective</th>
                    <th>Relative Scaling Formula</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Action Probability (GAF)</strong></td>
                    <td>Global occurrence density analysis.</td>
                    <td><code>Count(Action) / Total Logs</code></td>
                </tr>
                <tr>
                    <td><strong>User-Signature (UAF)</strong></td>
                    <td>Relationship mapping between UserID and ActionID.</td>
                    <td><code>Count(User, Action) / Total User Logs</code></td>
                </tr>
                <tr>
                    <td><strong>Device & Network Trust (DNF)</strong></td>
                    <td>Subnet and User-Agent signature trust profiling.</td>
                    <td><code>Count(User, Device) / Total User Logs</code></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>3. Algorithmic Calculation Logic</h2>
        <p>The final anomaly score is derived from the <strong>Average Path Length</strong> across an ensemble of 100 isolation trees. Each incoming log is evaluated against the <strong>Trusted Baseline</strong> to determine its uniqueness across the normalized vector dimensions.</p>
        
        <div class="highlight">
            <p>Relative Feature Value = Count(Attribute) / Total Reference Population (N)</p>
        </div>

        <div style="margin-top: 15px;">
            <p><strong>Dimension A: Global Action Probability</strong><br>
            The system calculates the relative probability of an action across all logs (<code>Count(Action) / Total Logs</code>). Common actions (e.g., 'Clock-In') generate high relative density, making them difficult to isolate. Rare actions create "sparse" regions, leading to faster isolation and higher anomaly scores.</p>
            
            <p><strong>Dimension B: User-Behavioral Alignment</strong><br>
            This dimension analyzes the consistency of a user's behavior relative to their total history (<code>Count(User, Action) / Total User Logs</code>). Actions falling outside a user's normal routine (e.g. an Intern executing an admin action) result in low relative frequencies, leading to rapid isolation.</p>
            
            <p><strong>Dimension C: Network & Device Fingerprinting</strong><br>
            The model treats the IP subnet and device user-agent as trust anchors. The relative device fingerprint probability (<code>Count(User, Agent) / Total User Logs</code>) identifies unauthorized logins from new devices. A brand-new device user-agent results in a relative value close to 0.0, creating an outlier in the vector space.</p>
        </div>
    </div>

    <div class="section">
        <h2>4. Risk Classification Standards</h2>
        <p>Scores are mapped to operational tiers based on the <strong>Standard Deviation</strong> from the baseline mean, guiding administrative response protocols.</p>
        <table>
            <thead>
                <tr>
                    <th>Alert Tier</th>
                    <th>Score Threshold</th>
                    <th>Operational Interpretation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: #ef4444;">Critical</strong></td>
                    <td>Above 0.70</td>
                    <td>Major structural outlier. Indicates high-confidence security anomaly.</td>
                </tr>
                <tr>
                    <td><strong style="color: #f59e0b;">Warning</strong></td>
                    <td>0.51 - 0.70</td>
                    <td>Minor statistical deviation. Potential "first-time" event or new environment.</td>
                </tr>
                <tr>
                    <td><strong style="color: #10b981;">Normal</strong></td>
                    <td>Below 0.50</td>
                    <td>High-density alignment. Behavior is consistent with the trusted history.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>5. Compliance & Industry Standards</h2>
        <div class="reference-box">
            <h4>Security Frameworks & Academic Foundations:</h4>
            
            <div class="ref-item">
                <strong>ISO/IEC 27001:2022 — Information Security Management</strong>
                <p style="text-align: left; margin-bottom: 2px;">SmarTern implements automated controls for Annex A 8.15 and 8.16, providing the anomaly detection required for modern information security management systems (ISMS).</p>
                <a class="ref-link" href="https://www.iso.org/standard/27001">https://www.iso.org/standard/27001</a>
            </div>

            <div class="ref-item">
                <strong>NIST SP 800-94 — IDPS Guidelines</strong>
                <p style="text-align: left; margin-bottom: 2px;">The system follows the National Institute of Standards and Technology's recommendations for behavioral-based detection to identify "Zero-Day" vulnerabilities that signature-based systems miss.</p>
                <a class="ref-link" href="https://csrc.nist.gov/publications/detail/sp/800-94/final">https://csrc.nist.gov/publications/detail/sp/800-94/final</a>
            </div>

            <div class="ref-item">
                <strong>Original Mathematical Foundation (Liu et al., 2012)</strong>
                <p style="text-align: left; margin-bottom: 2px;">This study validates the exact mathematical formulation of the path normalization function c(n) and the heuristic anomaly score calculation s(x, n) = 2^(-E(h(x))/c(n)) mapped in our security specification.</p>
                <a class="ref-link" href="https://doi.org/10.1145/2133360.2133363">Permanent DOI: https://doi.org/10.1145/2133360.2133363</a> &bull; 
                <a class="ref-link" href="https://www.semanticscholar.org/paper/Isolation-based-anomaly-detection-Liu-Ting/dbdbb148a0494be2340a6b738466b0f1ed039f93">Open Access Read-Link</a>
            </div>

            <div class="ref-item">
                <strong>Validation in Educational Big Data (Feng & Zhang, 2023)</strong>
                <p style="text-align: left; margin-bottom: 2px;">This paper validates the specific deployment of an unsupervised tree ensemble to track user behavioral alignments, unauthorized subnet logins, and irregular account footprints within a university infrastructure without historical threat tags.</p>
                <a class="ref-link" href="https://doi.org/10.37965/jait.2023.0445">Permanent DOI: https://doi.org/10.37965/jait.2023.0445</a> &bull; 
                <a class="ref-link" href="https://www.researchgate.net/publication/375845762_Optimizing_the_Isolation_Forest_Algorithm_for_Identifying_Abnormal_Behaviors_of_Students_in_Education_Management_Big_Data">Open Access Read-Link</a>
            </div>
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} SmarTern Internship Management System &bull; Technical Specification Document
    </div>
</body>
</html>
