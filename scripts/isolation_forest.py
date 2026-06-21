import sys
import json
import os
import numpy as np
from sklearn.ensemble import IsolationForest
from collections import Counter

# Path relative to the project root (where artisan is)
BASELINE_FILE = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'audit_baseline.json')

def load_baseline():
    if os.path.exists(BASELINE_FILE):
        try:
            with open(BASELINE_FILE, 'r') as f:
                return json.load(f)
        except Exception as e:
            sys.stderr.write(f"Error loading baseline: {e}\n")
    return []

def main():
    # Read input from Laravel
    input_data = sys.stdin.read().strip()
    if not input_data:
        return

    try:
        data = json.loads(input_data)
        incoming_logs = data.get('logs', [])
        
        if not incoming_logs:
            print(json.dumps({"scores": []}))
            return

        baseline_logs = load_baseline()
        all_logs = baseline_logs + incoming_logs

        def get_subnet(ip):
            if not ip or '.' not in ip: return '0.0.0.0'
            return '.'.join(ip.split('.')[:3]) + '.0'

        def get_subnet_b(ip):
            if not ip or '.' not in ip: return '0.0.0.0'
            return '.'.join(ip.split('.')[:2]) + '.0.0'

        def get_role_id(l):
            r = l.get('role_id')
            if r is None:
                user_rel = l.get('user')
                if isinstance(user_rel, dict):
                    r = user_rel.get('role_id')
            return int(r) if r is not None else 5

        role_ids = [get_role_id(log) for log in all_logs]
        role_totals = Counter(role_ids)

        actions = [log['action'] for log in all_logs]
        action_counts = Counter(actions)

        role_actions = [(role_ids[idx], log['action']) for idx, log in enumerate(all_logs)]
        role_action_counts = Counter(role_actions)

        user_actions = [(log['user_id'], log['action']) for log in all_logs]
        ua_counts = Counter(user_actions)

        subnets = [get_subnet(log.get('ip_address')) for log in all_logs]
        subnet_counts = Counter(subnets)

        subnets_b = [get_subnet_b(log.get('ip_address')) for log in all_logs]
        subnet_b_counts = Counter(subnets_b)

        def clean_user_agent(ua):
            if not ua: return 'Unknown'
            ua = ua.lower()
            os_name = 'Other-OS'
            if 'iphone' in ua or 'ipad' in ua: os_name = 'iOS'
            elif 'android' in ua: os_name = 'Android'
            elif 'windows' in ua: os_name = 'Windows'
            elif 'macintosh' in ua or 'mac os' in ua: os_name = 'MacOS'
            elif 'linux' in ua: os_name = 'Linux'
            
            browser_name = 'Other-Browser'
            if 'chrome' in ua or 'crios' in ua: browser_name = 'Chrome'
            elif 'firefox' in ua: browser_name = 'Firefox'
            elif 'safari' in ua and 'chrome' not in ua: browser_name = 'Safari'
            elif 'edge' in ua: browser_name = 'Edge'
            return f"{os_name}|{browser_name}"

        user_agents = [log.get('device') or clean_user_agent(log.get('user_agent', 'Unknown')) for log in all_logs]
        ua_agent_counts = Counter(user_agents)

        device_identities = [f"{log['user_id']}|{log.get('device') or clean_user_agent(log.get('user_agent', 'Unknown'))}" for log in all_logs]
        device_id_counts = Counter(device_identities)

        # Track how many logs each user has in the baseline to identify cold start users
        user_baseline_counts = Counter([log['user_id'] for log in baseline_logs])
        
        # Track Role + UserAgent combinations to fallback on for cold start users
        role_uas = [(role_ids[idx], user_agents[idx]) for idx in range(len(all_logs))]
        role_ua_counts = Counter(role_uas)

        total_logs = len(all_logs)
        user_totals = Counter([log['user_id'] for log in all_logs])

        def is_whitelisted(log):
            log_user_id = log.get('user_id')
            log_action = log.get('action')
            log_device = log.get('device') or clean_user_agent(log.get('user_agent', 'Unknown'))
            log_subnet = get_subnet(log.get('ip_address'))

            for b in baseline_logs:
                b_device = b.get('device') or clean_user_agent(b.get('user_agent', 'Unknown'))
                b_subnet = get_subnet(b.get('ip_address'))

                if (b.get('user_id') == log_user_id and
                    b.get('action') == log_action and
                    b_device == log_device and
                    b_subnet == log_subnet):
                    return True
            return False

        from datetime import datetime

        def get_hour_feature(l):
            ts = l.get('timestamp')
            if not ts:
                return 1.0
            try:
                if 'T' in ts:
                    dt = datetime.fromisoformat(ts.replace('Z', '+00:00'))
                else:
                    dt = datetime.strptime(ts.split('.')[0], "%Y-%m-%d %H:%M:%S")
                if 8 <= dt.hour <= 18:
                    return 1.0
                return 0.0
            except Exception:
                return 1.0

        features = []
        for i, log in enumerate(all_logs):
            u_id = log['user_id']
            u_total = user_totals[u_id] if user_totals[u_id] > 0 else 1
            r_id = role_ids[i]
            r_total = role_totals[r_id] if role_totals[r_id] > 0 else 1
            
            # Determine if the user is in cold-start phase (fewer than 5 baseline entries)
            is_cold_start = user_baseline_counts[u_id] < 5
            
            if is_cold_start:
                # Fallback to role averages to prevent 100% single-action outlier spikes
                user_action_feat = role_action_counts[(r_id, log['action'])] / r_total
                device_id_feat = role_ua_counts[(r_id, user_agents[i])] / r_total
            else:
                # Use user-specific baseline frequencies
                user_action_feat = ua_counts[(u_id, log['action'])] / u_total
                device_id_feat = device_id_counts[device_identities[i]] / u_total
            
            features.append([
                action_counts[log['action']] / total_logs,
                role_action_counts[(r_id, log['action'])] / r_total,
                user_action_feat,
                subnet_counts[subnets[i]] / total_logs,
                subnet_b_counts[subnets_b[i]] / total_logs,
                ua_agent_counts[user_agents[i]] / total_logs,
                device_id_feat,
                get_hour_feature(log)
            ])

        X = np.array(features)

        # 3. Configure Isolation Forest
        model = IsolationForest(
            n_estimators=100, 
            contamination='auto', 
            max_samples='auto',
            n_jobs=1, 
            random_state=42
        )
        model.fit(X)
        
        # Redundancy Reduction (Feng & Zhang, 2023): Prune highly correlated trees
        try:
            n_trees = len(model.estimators_)
            depths = np.zeros((X.shape[0], n_trees))
            for idx, estimator in enumerate(model.estimators_):
                indicator = estimator.decision_path(X)
                depths[:, idx] = np.asarray(indicator.sum(axis=1)).reshape(-1)
                
            corr = np.corrcoef(depths, rowvar=False)
            corr = np.nan_to_num(corr, nan=0.0)
            
            kept_indices = []
            threshold = 0.90
            for i in range(n_trees):
                is_redundant = False
                for j in kept_indices:
                    if corr[i, j] > threshold:
                        is_redundant = True
                        break
                if not is_redundant:
                    kept_indices.append(i)
                    
            if kept_indices:
                model.estimators_ = [model.estimators_[i] for i in kept_indices]
                model.n_estimators = len(kept_indices)
                if hasattr(model, '_estimators_features'):
                    model._estimators_features = [model._estimators_features[i] for i in kept_indices]
                if hasattr(model, '_estimators_samples'):
                    model._estimators_samples = [model._estimators_samples[i] for i in kept_indices]
                if hasattr(model, '_decision_path_lengths'):
                    model._decision_path_lengths = [model._decision_path_lengths[i] for i in kept_indices]
                if hasattr(model, '_average_path_length_per_tree'):
                    model._average_path_length_per_tree = [model._average_path_length_per_tree[i] for i in kept_indices]
        except Exception as e:
            pass
        
        # 4. Use score_samples for the PURE anomaly score s in [0, 1]
        raw_scores = model.score_samples(X)
        all_scores = (-raw_scores).tolist()
        
        incoming_scores = all_scores[len(baseline_logs):]

        # Calculate dynamic average normal score from the bottom 50% (least anomalous logs)
        sorted_scores = sorted(all_scores)
        avg_normal_score = sum(sorted_scores[:len(sorted_scores)//2]) / (len(sorted_scores)//2) if sorted_scores else 0.20

        # Whitelist override: set to the dynamically computed normal score range
        for idx, log in enumerate(incoming_logs):
            if is_whitelisted(log):
                incoming_scores[idx] = avg_normal_score

        # 5. Return ONLY the scores for incoming logs
        print(json.dumps({"scores": incoming_scores}))

    except Exception as e:
        sys.stderr.write(f"PYTHON_ERROR: {str(e)}")

if __name__ == "__main__":
    main()
