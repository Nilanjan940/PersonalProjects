import streamlit as st
import pandas as pd
import numpy as np
import plotly.graph_objects as go
from plotly.subplots import make_subplots
from datetime import datetime, timedelta
import time

# Set page configuration
st.set_page_config(
    page_title="Project Vajra Raksha",
    page_icon="🛡️",
    layout="wide",
    initial_sidebar_state="expanded"
)

# Custom CSS for enhanced styling
st.markdown("""
<style>
    /* Main styling */
    .main-header {
        font-size: 2.8rem;
        color: #4E5B31;
        text-align: center;
        margin-bottom: 1rem;
        font-weight: 700;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }
    .sub-header {
        font-size: 1.2rem;
        color: #9C8A6F;
        text-align: center;
        margin-bottom: 2rem;
        font-weight: 400;
    }
    
    /* Alert boxes */
    .alert-box-critical {
        background: linear-gradient(135deg, #ff4b4b 0%, #cc0000 100%);
        color: white;
        padding: 18px;
        border-radius: 8px;
        margin: 12px 0px;
        font-weight: bold;
        text-align: center;
        box-shadow: 0 4px 12px rgba(204,0,0,0.3);
        animation: blink 1.5s infinite;
        border-left: 6px solid #990000;
    }
    .alert-box-warning {
        background: linear-gradient(135deg, #FF9900 0%, #E58900 100%);
        color: white;
        padding: 16px;
        border-radius: 8px;
        margin: 12px 0px;
        font-weight: bold;
        text-align: center;
        box-shadow: 0 4px 10px rgba(255,153,0,0.2);
        border-left: 6px solid #CC7700;
    }
    .alert-box-normal {
        background: linear-gradient(135deg, #4CAF50 0%, #3A8C3E 100%);
        color: white;
        padding: 16px;
        border-radius: 8px;
        margin: 12px 0px;
        font-weight: bold;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,170,68,0.2);
        border-left: 6px solid #2D6B30;
    }
    
    /* Animations */
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    /* Cards and metrics */
    .metric-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px;
        border-radius: 10px;
        border-left: 5px solid #4E5B31;
        margin: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
    }
    
    /* Buttons */
    .stButton button {
        background: linear-gradient(135deg, #4E5B31 0%, #3A4524 100%);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 4px 8px rgba(78,91,49,0.2);
    }
    .stButton button:hover {
        background: linear-gradient(135deg, #3A4524 0%, #2D361B 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(78,91,49,0.3);
    }
    
    /* Sidebar */
    .sidebar .sidebar-content {
        background: linear-gradient(180deg, #3A4524 0%, #2D361B 100%);
        color: white;
        box-shadow: 3px 0 15px rgba(0,0,0,0.2);
    }
    
    /* Expander styling */
    .streamlit-expanderHeader {
        font-weight: 600;
        background-color: #f8f9fa;
        border-radius: 6px;
        padding: 12px;
        border-left: 4px solid #4E5B31;
    }
    
    /* Divider */
    .divider {
        height: 2px;
        background: linear-gradient(90deg, transparent, #4E5B31, transparent);
        margin: 20px 0;
        border: none;
    }
    
    /* Custom tabs */
    .custom-tab {
        padding: 10px 20px;
        border-radius: 6px;
        background-color: #f0f2f6;
        margin: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .custom-tab.active {
        background-color: #4E5B31;
        color: white;
        font-weight: bold;
    }
</style>
""", unsafe_allow_html=True)

# Initialize session state for authentication and other variables
if 'authenticated' not in st.session_state:
    st.session_state.authenticated = False
if 'current_view' not in st.session_state:
    st.session_state.current_view = "Dashboard"
if 'selected_drone' not in st.session_state:
    st.session_state.selected_drone = None

# Authentication function
def authenticate(username, password, otp):
    # Simple authentication for demo purposes
    if username == "army_user" and password == "VajraRaksha2023" and otp == "123456":
        return True
    return False

# Login form
def login_form():
    col1, col2, col3 = st.columns([1, 2, 1])
    with col2:
        st.markdown("<h1 class='main-header'>Project Vajra Raksha</h1>", unsafe_allow_html=True)
        st.markdown("<p class='sub-header'>Secure Drone Operations Monitoring System</p>", unsafe_allow_html=True)
        
        # Login card
        with st.container():
            st.markdown("### Secure Authentication Required")
            with st.form("login_form"):
                username = st.text_input("Service Number", placeholder="Enter your service number")
                password = st.text_input("Password", type="password", placeholder="Enter your passcode")
                otp = st.text_input("One-Time Password", placeholder="Enter OTP from token")
                submitted = st.form_submit_button("Authenticate")
                
                if submitted:
                    if authenticate(username, password, otp):
                        st.session_state.authenticated = True
                        st.rerun()
                    else:
                        st.error("Authentication failed. Please check your credentials.")

# Generate sample drone data with planned vs actual paths
def generate_drone_data(num_drones=8, hours=12):
    drones = []
    for i in range(num_drones):
        drone_id = f"DRN-{1000 + i}"
        base_lat = 32.7266 + np.random.uniform(-0.3, 0.3)
        base_lon = 74.8570 + np.random.uniform(-0.3, 0.3)
        
        # Generate telemetry data
        timestamps = [datetime.now() - timedelta(minutes=5*m) for m in range(hours*12, 0, -1)]
        altitude = np.random.normal(150, 20, len(timestamps))
        velocity = np.random.normal(25, 4, len(timestamps))
        battery = np.linspace(100, np.random.uniform(20, 50), len(timestamps))
        gps_drift = np.random.exponential(0.8, len(timestamps))
        
        # Generate planned vs actual path data
        planned_lats = [base_lat + np.sin(t/50) * 0.08 for t in range(len(timestamps))]
        planned_lons = [base_lon + np.cos(t/50) * 0.08 for t in range(len(timestamps))]
        
        actual_lats = [planned_lats[t] + np.random.normal(0, 0.003) for t in range(len(timestamps))]
        actual_lons = [planned_lons[t] + np.random.normal(0, 0.003) for t in range(len(timestamps))]
        
        # Introduce some anomalies
        anomalies = np.zeros(len(timestamps))
        anomaly_types = []
        
        if np.random.random() < 0.35:  # 35% chance of having anomalies
            anomaly_points = np.random.choice(range(10, len(timestamps)-1), size=np.random.randint(1, 3), replace=False)
            for point in anomaly_points:
                anomalies[point] = np.random.choice([1, 2, 3])  # 1: Low, 2: Medium, 3: High severity
                
                # Determine anomaly type
                anomaly_type = np.random.choice(["GPS Spoofing", "Battery Drain", "Signal Jamming", "Unauthorized Diversion", "Communication Loss"])
                confidence = np.random.uniform(0.75, 0.97)
                
                anomaly_types.append({
                    "timestamp": timestamps[point],
                    "type": anomaly_type,
                    "severity": anomalies[point],
                    "confidence": confidence
                })
                
                # Adjust telemetry based on anomaly type
                if anomaly_type == "GPS Spoofing":
                    actual_lats[point] += np.random.uniform(-0.015, 0.015)
                    actual_lons[point] += np.random.uniform(-0.015, 0.015)
                    gps_drift[point] += np.random.uniform(4, 12)
                elif anomaly_type == "Battery Drain":
                    battery[point:] -= np.random.uniform(4, 12)
                    if battery[point] < 0:
                        battery[point] = 0
                elif anomaly_type == "Signal Jamming":
                    gps_drift[point] += np.random.uniform(8, 18)
                    velocity[point] += np.random.uniform(-4, 4)
                elif anomaly_type == "Unauthorized Diversion":
                    actual_lats[point:] = [l + np.random.uniform(-0.008, 0.008) for l in actual_lats[point:]]
                    actual_lons[point:] = [l + np.random.uniform(-0.008, 0.008) for l in actual_lons[point:]]
                elif anomaly_type == "Communication Loss":
                    # Simulate communication loss
                    pass
        
        # Add mission type
        mission_types = ["Surveillance", "Reconnaissance", "Border Patrol", "Target Tracking"]
        mission_type = np.random.choice(mission_types)
        
        drones.append({
            'id': drone_id,
            'call_sign': f"HAWK-{i+1}",
            'status': 'Active' if np.random.random() > 0.15 else 'Maintenance',
            'mission_type': mission_type,
            'base_lat': base_lat,
            'base_lon': base_lon,
            'current_lat': actual_lats[-1],
            'current_lon': actual_lons[-1],
            'timestamps': timestamps,
            'altitude': altitude,
            'velocity': velocity,
            'battery': battery,
            'gps_drift': gps_drift,
            'anomalies': anomalies,
            'anomaly_details': anomaly_types,
            'planned_lats': planned_lats,
            'planned_lons': planned_lons,
            'actual_lats': actual_lats,
            'actual_lons': actual_lons,
            'last_contact': datetime.now() - timedelta(minutes=np.random.randint(0, 5))
        })
    
    return drones

# Dashboard header
def render_header():
    col1, col2, col3 = st.columns([1, 3, 1])
    with col2:
        st.markdown("<h1 class='main-header'>Project Vajra Raksha</h1>", unsafe_allow_html=True)
        st.markdown("<p class='sub-header'>AI-Powered Drone Anomaly Detection System</p>", unsafe_allow_html=True)
        
        # Current time and operation status
        now = datetime.now()
        col1, col2, col3 = st.columns(3)
        with col1:
            st.info(f"**System Time:** {now.strftime('%H:%M:%S')}")
        with col2:
            st.info(f"**Operation:** Jammu Sector")
        with col3:
            st.info(f"**Grid:** NF-942-835")

# Alert system
def render_alert_system(drones):
    critical_anomalies = []
    warning_anomalies = []
    
    for drone in drones:
        for anomaly in drone['anomaly_details']:
            if anomaly['severity'] == 3:  # High severity
                critical_anomalies.append({
                    'drone': drone['call_sign'],
                    'id': drone['id'],
                    'type': anomaly['type'],
                    'confidence': anomaly['confidence'],
                    'timestamp': anomaly['timestamp']
                })
            elif anomaly['severity'] == 2:  # Medium severity
                warning_anomalies.append({
                    'drone': drone['call_sign'],
                    'id': drone['id'],
                    'type': anomaly['type'],
                    'confidence': anomaly['confidence'],
                    'timestamp': anomaly['timestamp']
                })
    
    if critical_anomalies:
        with st.container():
            st.markdown(f'<div class="alert-box-critical">🚨 CRITICAL ALERT: {len(critical_anomalies)} high-severity anomalies detected</div>', unsafe_allow_html=True)
            for anomaly in critical_anomalies:
                st.write(f"- **{anomaly['drone']}** ({anomaly['id']}): {anomaly['type']} (Confidence: {anomaly['confidence']*100:.1f}%) at {anomaly['timestamp'].strftime('%H:%M:%S')}")
            
            # One-click recommendations for critical anomalies
            st.subheader("Recommended Actions")
            col1, col2, col3, col4 = st.columns(4)
            with col1:
                if st.button("Return to Base", key="rtb_btn", help="Order affected drones to return to base immediately"):
                    st.success("Return to Base command sent to affected drones")
            with col2:
                if st.button("Emergency Landing", key="eland_btn", help="Initiate emergency landing protocol"):
                    st.success("Emergency Landing command sent to affected drones")
            with col3:
                if st.button("Secure Comms", key="comms_btn", help="Activate secure communication channels"):
                    st.success("Secure communication protocol activated")
            with col4:
                if st.button("Deploy Countermeasures", key="cm_btn", help="Deploy electronic countermeasures"):
                    st.success("Countermeasures deployed for affected drones")
    
    elif warning_anomalies:
        with st.container():
            st.markdown(f'<div class="alert-box-warning">⚠️ WARNING: {len(warning_anomalies)} medium-severity anomalies detected</div>', unsafe_allow_html=True)
            for anomaly in warning_anomalies:
                st.write(f"- **{anomaly['drone']}** ({anomaly['id']}): {anomaly['type']} (Confidence: {anomaly['confidence']*100:.1f}%) at {anomaly['timestamp'].strftime('%H:%M:%S')}")
            
            # Recommendations for warnings
            st.info("Monitor these drones closely. Consider initiating diagnostic checks or preparing contingency plans.")
    else:
        st.markdown('<div class="alert-box-normal">✅ SYSTEM STATUS: NORMAL - All drones operating within parameters</div>', unsafe_allow_html=True)

# Fleet metrics
def render_fleet_metrics(drones):
    st.subheader("Fleet Overview")
    
    active_drones = len([d for d in drones if d['status'] == 'Active'])
    maintenance_drones = len([d for d in drones if d['status'] == 'Maintenance'])
    anomaly_count = sum([len(d['anomaly_details']) for d in drones])
    critical_count = len([a for d in drones for a in d['anomaly_details'] if a['severity'] == 3])
    warning_count = len([a for d in drones for a in d['anomaly_details'] if a['severity'] == 2])
    avg_battery = np.mean([d['battery'][-1] for d in drones if d['status'] == 'Active'])
    avg_altitude = np.mean([d['altitude'][-1] for d in drones if d['status'] == 'Active'])
    
    col1, col2, col3, col4, col5, col6 = st.columns(6)
    
    with col1:
        st.metric("Total Drones", len(drones), help="All drones in the fleet")
    with col2:
        st.metric("Active Drones", active_drones, help="Drones currently operational")
    with col3:
        st.metric("Critical Alerts", critical_count, delta=None, help="High severity anomalies detected")
    with col4:
        st.metric("Warnings", warning_count, delta=None, help="Medium severity anomalies detected")
    with col5:
        st.metric("Avg Battery", f"{avg_battery:.1f}%", help="Average battery level across active drones")
    with col6:
        st.metric("Avg Altitude", f"{avg_altitude:.1f} m", help="Average altitude of active drones")
    
    return active_drones, maintenance_drones, anomaly_count, avg_battery

# Interactive map
def render_drone_map(drones):
    st.subheader("Live Operations Map - Planned vs Actual Paths")
    
    # Create a Plotly map with planned vs actual paths
    fig = go.Figure()
    
    for drone in drones:
        # Determine color based on status and anomalies
        if any(anom['severity'] == 3 for anom in drone['anomaly_details']):
            color = 'red'
            size = 14
        elif any(anom['severity'] == 2 for anom in drone['anomaly_details']):
            color = 'orange'
            size = 12
        elif drone['status'] == 'Active':
            color = 'green'
            size = 10
        else:
            color = 'gray'
            size = 10
        
        # Add planned path
        fig.add_trace(go.Scattermapbox(
            lat=drone['planned_lats'],
            lon=drone['planned_lons'],
            mode='lines',
            line=dict(width=2, color='blue', dash='dash'),
            name=f"{drone['call_sign']} Planned",
            hoverinfo='text',
            text=f"Planned Path: {drone['call_sign']}",
            showlegend=False
        ))
        
        # Add actual path
        fig.add_trace(go.Scattermapbox(
            lat=drone['actual_lats'],
            lon=drone['actual_lons'],
            mode='lines',
            line=dict(width=3, color=color),
            name=f"{drone['call_sign']} Actual",
            hoverinfo='text',
            text=f"Actual Path: {drone['call_sign']}",
            showlegend=False
        ))
        
        # Add current position
        fig.add_trace(go.Scattermapbox(
            lat=[drone['current_lat']],
            lon=[drone['current_lon']],
            mode='markers',
            marker=dict(size=size, color=color),
            name=f"{drone['call_sign']} Current",
            hoverinfo='text',
            text=f"{drone['call_sign']} - {drone['id']}<br>Status: {drone['status']}<br>Mission: {drone['mission_type']}<br>Battery: {drone['battery'][-1]:.1f}%<br>Anomalies: {len(drone['anomaly_details'])}",
            showlegend=False
        ))
    
    # Update map layout
    fig.update_layout(
        mapbox=dict(
            style="open-street-map",
            center=dict(lat=32.7266, lon=74.8570),
            zoom=9.2,
            bearing=0,
            pitch=0
        ),
        height=500,
        margin={"r":0,"t":0,"l":0,"b":0},
        autosize=True
    )
    
    # Add legend manually
    fig.add_trace(go.Scattermapbox(
        lat=[None],
        lon=[None],
        mode='markers',
        marker=dict(size=10, color='red'),
        name='Critical',
        showlegend=True
    ))
    
    fig.add_trace(go.Scattermapbox(
        lat=[None],
        lon=[None],
        mode='markers',
        marker=dict(size=10, color='orange'),
        name='Warning',
        showlegend=True
    ))
    
    fig.add_trace(go.Scattermapbox(
        lat=[None],
        lon=[None],
        mode='markers',
        marker=dict(size=10, color='green'),
        name='Normal',
        showlegend=True
    ))
    
    fig.add_trace(go.Scattermapbox(
        lat=[None],
        lon=[None],
        mode='lines',
        line=dict(width=2, color='blue', dash='dash'),
        name='Planned Path',
        showlegend=True
    ))
    
    st.plotly_chart(fig, use_container_width=True)

# Drone status panels
def render_drone_status(drones):
    st.subheader("Drone Status Details")
    
    # Create tabs for different views
    tab1, tab2, tab3 = st.tabs(["All Drones", "Active Missions", "Anomaly Detected"])
    
    with tab1:
        cols = st.columns(2)
        for i, drone in enumerate(drones):
            with cols[i % 2]:
                render_drone_card(drone)
    
    with tab2:
        active_drones = [d for d in drones if d['status'] == 'Active']
        if active_drones:
            for drone in active_drones:
                render_drone_card(drone)
        else:
            st.info("No active missions currently.")
    
    with tab3:
        anomaly_drones = [d for d in drones if d['anomaly_details']]
        if anomaly_drones:
            for drone in anomaly_drones:
                render_drone_card(drone)
        else:
            st.success("No anomalies detected in any drones.")

# Individual drone card
def render_drone_card(drone):
    # Determine status color and text
    if any(anom['severity'] == 3 for anom in drone['anomaly_details']):
        status_color = "red"
        status_text = "CRITICAL"
    elif any(anom['severity'] == 2 for anom in drone['anomaly_details']):
        status_color = "orange"
        status_text = "WARNING"
    elif drone['status'] == 'Active':
        status_color = "green"
        status_text = "NORMAL"
    else:
        status_color = "gray"
        status_text = "MAINTENANCE"
    
    # Create expandable section for each drone
    with st.expander(f"{drone['call_sign']} - {status_text} | {drone['mission_type']}", expanded=(status_color == "red")):
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            st.metric("Battery", f"{drone['battery'][-1]:.1f}%")
        with col2:
            st.metric("Altitude", f"{drone['altitude'][-1]:.1f} m")
        with col3:
            st.metric("Velocity", f"{drone['velocity'][-1]:.1f} m/s")
        with col4:
            st.metric("GPS Drift", f"{drone['gps_drift'][-1]:.2f}")
        
        # Last contact information
        st.caption(f"Last contact: {drone['last_contact'].strftime('%H:%M:%S')}")
        
        # Show anomaly details if any
        if drone['anomaly_details']:
            st.markdown("---")
            st.subheader("Detected Anomalies")
            for anomaly in drone['anomaly_details']:
                severity_text = "CRITICAL" if anomaly['severity'] == 3 else "WARNING" if anomaly['severity'] == 2 else "LOW"
                severity_color = "red" if anomaly['severity'] == 3 else "orange" if anomaly['severity'] == 2 else "yellow"
                
                st.markdown(f"""
                <div style='border-left: 5px solid {severity_color}; padding-left: 12px; margin: 12px 0;'>
                    <b>Type:</b> {anomaly['type']}<br>
                    <b>Severity:</b> <span style='color: {severity_color}; font-weight: bold;'>{severity_text}</span><br>
                    <b>Confidence:</b> {anomaly['confidence']*100:.1f}%<br>
                    <b>Time:</b> {anomaly['timestamp'].strftime('%H:%M:%S')}
                </div>
                """, unsafe_allow_html=True)
                
                # Contextual recommendations based on anomaly type
                if anomaly['type'] == "GPS Spoofing":
                    st.info("**Recommended action:** Verify GPS signals, switch to inertial navigation, return to base")
                elif anomaly['type'] == "Battery Drain":
                    st.info("**Recommended action:** Check power systems, return to base immediately")
                elif anomaly['type'] == "Signal Jamming":
                    st.info("**Recommended action:** Switch to secure communication channels, deploy countermeasures")
                elif anomaly['type'] == "Unauthorized Diversion":
                    st.info("**Recommended action:** Regain control, return to planned route, secure systems")
                elif anomaly['type'] == "Communication Loss":
                    st.info("**Recommended action:** Attempt reconnection, activate backup systems, return to base")
        
        # One-click action buttons for each drone
        st.markdown("---")
        st.subheader("Drone Actions")
        col1, col2, col3, col4 = st.columns(4)
        with col1:
            if st.button("Return to Base", key=f"rtb_{drone['id']}", help="Order drone to return to base"):
                st.success(f"Return to Base command sent to {drone['call_sign']}")
        with col2:
            if st.button("Emergency Landing", key=f"eland_{drone['id']}", help="Initiate emergency landing protocol"):
                st.success(f"Emergency Landing command sent to {drone['call_sign']}")
        with col3:
            if st.button("Secure Comms", key=f"comms_{drone['id']}", help="Activate secure communication"):
                st.success(f"Secure communication activated for {drone['call_sign']}")
        with col4:
            if st.button("Full Diagnostics", key=f"diag_{drone['id']}", help="Run complete diagnostic check"):
                st.success(f"Diagnostics initiated for {drone['call_sign']}")

# Telemetry analysis
def render_telemetry_analysis(drones):
    st.subheader("Detailed Telemetry Analysis")
    
    # Drone selector
    drone_options = [f"{d['call_sign']} ({d['id']})" for d in drones]
    selected_drone = st.selectbox("Select Drone for Detailed Analysis", drone_options, index=0)
    
    # Extract drone ID from selection
    drone_id = selected_drone.split('(')[1].replace(')', '')
    drone = next(d for d in drones if d['id'] == drone_id)
    
    # Create telemetry charts
    fig = make_subplots(
        rows=2, cols=2,
        subplot_titles=('Altitude', 'Velocity', 'Battery Level', 'GPS Drift'),
        specs=[[{"secondary_y": False}, {"secondary_y": False}],
               [{"secondary_y": False}, {"secondary_y": False}]]
    )
    
    # Add traces
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['altitude'], name='Altitude', line=dict(color='#4E5B31')),
        row=1, col=1
    )
    
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['velocity'], name='Velocity', line=dict(color='#9C8A6F')),
        row=1, col=2
    )
    
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['battery'], name='Battery', line=dict(color='#CC0000')),
        row=2, col=1
    )
    
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['gps_drift'], name='GPS Drift', line=dict(color='#FF9900')),
        row=2, col=2
    )
    
    # Highlight anomalies
    for anomaly in drone['anomaly_details']:
        # Find the closest timestamp index
        idx = min(range(len(drone['timestamps'])), key=lambda i: abs(drone['timestamps'][i] - anomaly['timestamp']))
        
        severity_color = "red" if anomaly['severity'] == 3 else "orange" if anomaly['severity'] == 2 else "yellow"
        
        fig.add_vrect(
            x0=drone['timestamps'][idx], x1=drone['timestamps'][idx] + timedelta(minutes=10),
            fillcolor=severity_color, opacity=0.2, line_width=0,
            row="all", col="all",
            annotation_text=anomaly['type'], annotation_position="top left"
        )
    
    fig.update_layout(
        height=600, 
        showlegend=False, 
        title_text=f"Telemetry Data for {drone['call_sign']}",
        plot_bgcolor='rgba(0,0,0,0)',
        paper_bgcolor='rgba(0,0,0,0)'
    )
    
    st.plotly_chart(fig, use_container_width=True)
    
    # Additional analysis
    if drone['anomaly_details']:
        st.info(f"**Analysis:** {len(drone['anomaly_details'])} anomalies detected. Review highlighted areas for details.")
    else:
        st.success("**Analysis:** No anomalies detected. All systems operating within normal parameters.")

# Main dashboard
def main_dashboard():
    # Sidebar
    with st.sidebar:
        st.image("https://img.icons8.com/fluency/96/drone.png", width=80)
        st.title("Command Center")
        
        # Navigation
        st.subheader("Navigation")
        view_options = ["Dashboard", "Live Tracking", "Anomaly Analysis", "Drone Fleet", "System Settings"]
        for view in view_options:
            if st.button(view, key=f"btn_{view}", use_container_width=True):
                st.session_state.current_view = view
        
        st.markdown("---")
        
        # Filter options
        st.subheader("Filters")
        status_filter = st.multiselect(
            "Drone Status",
            ["Active", "Maintenance", "Anomaly Detected"],
            default=["Active", "Anomaly Detected"]
        )
        
        mission_filter = st.multiselect(
            "Mission Type",
            ["Surveillance", "Reconnaissance", "Border Patrol", "Target Tracking"],
            default=["Surveillance", "Reconnaissance", "Border Patrol", "Target Tracking"]
        )
        
        st.markdown("---")
        
        # System controls
        st.subheader("System Controls")
        if st.button("Refresh Data", use_container_width=True):
            st.rerun()
            
        if st.button("Export Report", use_container_width=True):
            st.success("Report exported successfully")
            
        if st.button("Emergency Protocol", use_container_width=True):
            st.warning("Emergency protocol activated")
        
        st.markdown("---")
        
        # User info and logout
        st.info("Logged in as: **Col. Dhaval Singh**")
        if st.button("Logout", use_container_width=True):
            st.session_state.authenticated = False
            st.rerun()
    
    # Main content area
    render_header()
    
    # Generate drone data
    drones = generate_drone_data()
    
    # Alert system
    render_alert_system(drones)
    
    # Fleet metrics
    render_fleet_metrics(drones)
    
    # Map view
    render_drone_map(drones)
    
    # Drone status
    render_drone_status(drones)
    
    # Telemetry analysis
    render_telemetry_analysis(drones)
    
    # Footer
    st.markdown("---")
    col1, col2, col3 = st.columns([1, 2, 1])
    with col2:
        st.markdown("**Project Vajra Raksha** - AI for National Security | © 2023")
        st.markdown("*CLASSIFIED: FOR AUTHORIZED PERSONNEL ONLY - INDIAN ARMY USE ONLY*")

# Run the app
if not st.session_state.authenticated:
    login_form()
else:
    main_dashboard()