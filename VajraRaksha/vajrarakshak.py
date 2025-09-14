import streamlit as st
import pandas as pd
import numpy as np
import plotly.graph_objects as go
from plotly.subplots import make_subplots
import time
from datetime import datetime, timedelta

# Set page configuration
st.set_page_config(
    page_title="Project Vajra Raksha",
    page_icon="🛡️",
    layout="wide",
    initial_sidebar_state="expanded"
)

# Custom CSS for styling
st.markdown("""
<style>
    .main-header {
        font-size: 2.5rem;
        color: #4E5B31;
        text-align: center;
        margin-bottom: 2rem;
    }
    .alert-box-critical {
        background-color: #ff4b4b;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0px;
        font-weight: bold;
        text-align: center;
        animation: blink 1s infinite;
    }
    .alert-box-warning {
        background-color: #FF9900;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0px;
        font-weight: bold;
        text-align: center;
    }
    .alert-box-normal {
        background-color: #4CAF50;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0px;
        font-weight: bold;
        text-align: center;
    }
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    .metric-card {
        background-color: #f0f2f6;
        padding: 10px;
        border-radius: 5px;
        border-left: 5px solid #4E5B31;
        margin: 5px;
    }
    .sidebar .sidebar-content {
        background-color: #3A4524;
        color: white;
    }
    .stButton button {
        background-color: #4E5B31;
        color: white;
    }
    .action-button {
        background-color: #4E5B31;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        margin: 5px;
        font-weight: bold;
    }
    .action-button:hover {
        background-color: #3A4524;
    }
    .drone-path {
        stroke-dasharray: 5,5;
    }
</style>
""", unsafe_allow_html=True)

# Initialize session state for authentication
if 'authenticated' not in st.session_state:
    st.session_state.authenticated = False

# Authentication function
def authenticate(username, password, otp):
    # Simple authentication for demo purposes
    # In a real application, this would connect to a proper authentication service
    if username == "army_user" and password == "VajraRaksha2023" and otp == "123456":
        return True
    return False

# Login form
def login_form():
    st.markdown("<h1 class='main-header'>Project Vajra Raksha</h1>", unsafe_allow_html=True)
    st.markdown("### Secure Authentication Required")
    
    with st.form("login_form"):
        username = st.text_input("Service Number")
        password = st.text_input("Password", type="password")
        otp = st.text_input("One-Time Password")
        submitted = st.form_submit_button("Authenticate")
        
        if submitted:
            if authenticate(username, password, otp):
                st.session_state.authenticated = True
                st.rerun()
            else:
                st.error("Authentication failed. Please check your credentials.")

# Generate sample drone data with planned vs actual paths
def generate_drone_data(num_drones=10, hours=24):
    drones = []
    for i in range(num_drones):
        drone_id = f"DRN-{1000 + i}"
        base_lat = 32.7266 + np.random.uniform(-0.5, 0.5)
        base_lon = 74.8570 + np.random.uniform(-0.5, 0.5)
        
        # Generate telemetry data
        timestamps = [datetime.now() - timedelta(minutes=5*m) for m in range(hours*12, 0, -1)]
        altitude = np.random.normal(150, 30, len(timestamps))
        velocity = np.random.normal(25, 5, len(timestamps))
        battery = np.linspace(100, np.random.uniform(10, 40), len(timestamps))
        gps_drift = np.random.exponential(1, len(timestamps))
        
        # Generate planned vs actual path data
        planned_lats = [base_lat + np.sin(t/50) * 0.1 for t in range(len(timestamps))]
        planned_lons = [base_lon + np.cos(t/50) * 0.1 for t in range(len(timestamps))]
        
        actual_lats = [planned_lats[t] + np.random.normal(0, 0.005) for t in range(len(timestamps))]
        actual_lons = [planned_lons[t] + np.random.normal(0, 0.005) for t in range(len(timestamps))]
        
        # Introduce some anomalies
        anomalies = np.zeros(len(timestamps))
        anomaly_types = []
        anomaly_confidences = []
        
        if np.random.random() < 0.3:  # 30% chance of having anomalies
            anomaly_points = np.random.choice(range(5, len(timestamps)-1), size=np.random.randint(1, 4), replace=False)
            for point in anomaly_points:
                anomalies[point] = np.random.choice([1, 2, 3])  # 1: Low, 2: Medium, 3: High severity
                
                # Determine anomaly type
                anomaly_type = np.random.choice(["GPS Spoofing", "Battery Drain", "Signal Jamming", "Unauthorized Diversion"])
                anomaly_types.append({
                    "timestamp": timestamps[point],
                    "type": anomaly_type,
                    "severity": anomalies[point],
                    "confidence": np.random.uniform(0.7, 0.95)
                })
                
                # Adjust telemetry based on anomaly type
                if anomaly_type == "GPS Spoofing":
                    actual_lats[point] += np.random.uniform(-0.02, 0.02)
                    actual_lons[point] += np.random.uniform(-0.02, 0.02)
                    gps_drift[point] += np.random.uniform(5, 15)
                elif anomaly_type == "Battery Drain":
                    battery[point:] -= np.random.uniform(5, 15)
                    if battery[point] < 0:
                        battery[point] = 0
                elif anomaly_type == "Signal Jamming":
                    gps_drift[point] += np.random.uniform(10, 20)
                    velocity[point] += np.random.uniform(-5, 5)
                elif anomaly_type == "Unauthorized Diversion":
                    actual_lats[point:] = [l + np.random.uniform(-0.01, 0.01) for l in actual_lats[point:]]
                    actual_lons[point:] = [l + np.random.uniform(-0.01, 0.01) for l in actual_lons[point:]]
        
        drones.append({
            'id': drone_id,
            'call_sign': f"HAWK-{i+1}",
            'status': 'Active' if np.random.random() > 0.2 else 'Maintenance',
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
            'actual_lons': actual_lons
        })
    
    return drones

# Main dashboard
def main_dashboard():
    # Sidebar
    st.sidebar.image("https://img.icons8.com/fluency/96/drone.png", width=80)
    st.sidebar.title("Control Panel")
    
    # Sidebar options
    view_option = st.sidebar.selectbox(
        "Select View",
        ["Dashboard", "Live Tracking", "Anomaly Analysis", "Drone Fleet", "System Settings"]
    )
    
    # Filter options
    st.sidebar.subheader("Filters")
    status_filter = st.sidebar.multiselect(
        "Drone Status",
        ["Active", "Maintenance", "Anomaly Detected"],
        default=["Active"]
    )
    
    # Generate drone data
    drones = generate_drone_data()
    
    # Header
    col1, col2, col3 = st.columns([1, 2, 1])
    with col2:
        st.markdown("<h1 class='main-header'>Project Vajra Raksha</h1>", unsafe_allow_html=True)
        st.markdown("### AI-Powered Drone Anomaly Detection System")
    
    # Alert panel with multi-level warnings
    critical_anomalies = []
    warning_anomalies = []
    
    for drone in drones:
        for anomaly in drone['anomaly_details']:
            if anomaly['severity'] == 3:  # High severity
                critical_anomalies.append({
                    'drone': drone['call_sign'],
                    'type': anomaly['type'],
                    'confidence': anomaly['confidence'],
                    'timestamp': anomaly['timestamp']
                })
            elif anomaly['severity'] == 2:  # Medium severity
                warning_anomalies.append({
                    'drone': drone['call_sign'],
                    'type': anomaly['type'],
                    'confidence': anomaly['confidence'],
                    'timestamp': anomaly['timestamp']
                })
    
    if critical_anomalies:
        with st.container():
            st.markdown(f'<div class="alert-box-critical">🚨 CRITICAL ALERT: {len(critical_anomalies)} high-severity anomalies detected</div>', unsafe_allow_html=True)
            for anomaly in critical_anomalies:
                st.write(f"- {anomaly['drone']}: {anomaly['type']} (Confidence: {anomaly['confidence']*100:.1f}%) at {anomaly['timestamp'].strftime('%H:%M:%S')}")
            
            # One-click recommendations for critical anomalies
            st.subheader("Recommended Actions")
            col1, col2, col3, col4 = st.columns(4)
            with col1:
                if st.button("Return to Base", key="rtb_btn"):
                    st.success("Return to Base command sent to affected drones")
            with col2:
                if st.button("Emergency Landing", key="eland_btn"):
                    st.success("Emergency Landing command sent to affected drones")
            with col3:
                if st.button("Secure Comms", key="comms_btn"):
                    st.success("Secure communication protocol activated")
            with col4:
                if st.button("Deploy Countermeasures", key="cm_btn"):
                    st.success("Countermeasures deployed for affected drones")
    
    elif warning_anomalies:
        with st.container():
            st.markdown(f'<div class="alert-box-warning">⚠️ WARNING: {len(warning_anomalies)} medium-severity anomalies detected</div>', unsafe_allow_html=True)
            for anomaly in warning_anomalies:
                st.write(f"- {anomaly['drone']}: {anomaly['type']} (Confidence: {anomaly['confidence']*100:.1f}%) at {anomaly['timestamp'].strftime('%H:%M:%S')}")
    else:
        st.markdown('<div class="alert-box-normal">✅ SYSTEM STATUS: NORMAL</div>', unsafe_allow_html=True)
    
    # Metrics
    st.subheader("Fleet Overview")
    col1, col2, col3, col4, col5 = st.columns(5)
    
    active_drones = len([d for d in drones if d['status'] == 'Active'])
    anomaly_count = sum([len(d['anomaly_details']) for d in drones])
    critical_count = len(critical_anomalies)
    warning_count = len(warning_anomalies)
    avg_battery = np.mean([d['battery'][-1] for d in drones if d['status'] == 'Active'])
    
    with col1:
        st.metric("Total Drones", len(drones))
    with col2:
        st.metric("Active Drones", active_drones)
    with col3:
        st.metric("Critical Alerts", critical_count)
    with col4:
        st.metric("Warnings", warning_count)
    with col5:
        st.metric("Avg Battery", f"{avg_battery:.1f}%")
    
    # Enhanced Map view with planned vs actual paths
    st.subheader("Live Operations Map - Planned vs Actual Paths")
    
    # Create a Plotly map with planned vs actual paths
    fig = go.Figure()
    
    for drone in drones:
        # Add planned path
        fig.add_trace(go.Scattermapbox(
            lat=drone['planned_lats'],
            lon=drone['planned_lons'],
            mode='lines',
            line=dict(width=2, color='green', dash='dash'),
            name=f"{drone['call_sign']} Planned",
            hoverinfo='text',
            text=f"Planned Path: {drone['call_sign']}"
        ))
        
        # Add actual path
        fig.add_trace(go.Scattermapbox(
            lat=drone['actual_lats'],
            lon=drone['actual_lons'],
            mode='lines',
            line=dict(width=3, color='red' if len(drone['anomaly_details']) > 0 else 'blue'),
            name=f"{drone['call_sign']} Actual",
            hoverinfo='text',
            text=f"Actual Path: {drone['call_sign']}"
        ))
        
        # Add current position
        fig.add_trace(go.Scattermapbox(
            lat=[drone['current_lat']],
            lon=[drone['current_lon']],
            mode='markers',
            marker=dict(size=12, color='red' if len(drone['anomaly_details']) > 0 else 'green'),
            name=f"{drone['call_sign']} Current",
            hoverinfo='text',
            text=f"{drone['call_sign']} - {drone['id']}<br>Status: {drone['status']}<br>Battery: {drone['battery'][-1]:.1f}%<br>Anomalies: {len(drone['anomaly_details'])}"
        ))
    
    # Update map layout
    fig.update_layout(
        mapbox=dict(
            style="open-street-map",
            center=dict(lat=32.7266, lon=74.8570),
            zoom=9
        ),
        height=500,
        margin={"r":0,"t":0,"l":0,"b":0},
        showlegend=False
    )
    
    st.plotly_chart(fig, use_container_width=True)
    
    # Drone status grid with more detailed information
    st.subheader("Drone Status with Anomaly Details")
    
    for i, drone in enumerate(drones):
        # Determine status color
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
        with st.expander(f"{drone['call_sign']} - {status_text}", expanded=(status_color == "red")):
            col1, col2, col3, col4 = st.columns(4)
            
            with col1:
                st.metric("Battery", f"{drone['battery'][-1]:.1f}%")
            with col2:
                st.metric("Altitude", f"{drone['altitude'][-1]:.1f} m")
            with col3:
                st.metric("Velocity", f"{drone['velocity'][-1]:.1f} m/s")
            with col4:
                st.metric("GPS Drift", f"{drone['gps_drift'][-1]:.2f}")
            
            # Show anomaly details if any
            if drone['anomaly_details']:
                st.subheader("Detected Anomalies")
                for anomaly in drone['anomaly_details']:
                    severity_text = "CRITICAL" if anomaly['severity'] == 3 else "WARNING" if anomaly['severity'] == 2 else "LOW"
                    severity_color = "red" if anomaly['severity'] == 3 else "orange" if anomaly['severity'] == 2 else "yellow"
                    
                    st.markdown(f"""
                    <div style='border-left: 5px solid {severity_color}; padding-left: 10px; margin: 10px 0;'>
                        <b>Type:</b> {anomaly['type']}<br>
                        <b>Severity:</b> <span style='color: {severity_color};'>{severity_text}</span><br>
                        <b>Confidence:</b> {anomaly['confidence']*100:.1f}%<br>
                        <b>Time:</b> {anomaly['timestamp'].strftime('%H:%M:%S')}
                    </div>
                    """, unsafe_allow_html=True)
                    
                    # Contextual recommendations based on anomaly type
                    if anomaly['type'] == "GPS Spoofing":
                        st.info("Recommended action: Verify GPS signals, switch to inertial navigation, return to base")
                    elif anomaly['type'] == "Battery Drain":
                        st.info("Recommended action: Check power systems, return to base immediately")
                    elif anomaly['type'] == "Signal Jamming":
                        st.info("Recommended action: Switch to secure communication channels, deploy countermeasures")
                    elif anomaly['type'] == "Unauthorized Diversion":
                        st.info("Recommended action: Regain control, return to planned route, secure systems")
            
            # One-click action buttons for each drone
            st.subheader("Quick Actions")
            col1, col2, col3, col4 = st.columns(4)
            with col1:
                if st.button("Return to Base", key=f"rtb_{drone['id']}"):
                    st.success(f"Return to Base command sent to {drone['call_sign']}")
            with col2:
                if st.button("Emergency Landing", key=f"eland_{drone['id']}"):
                    st.success(f"Emergency Landing command sent to {drone['call_sign']}")
            with col3:
                if st.button("Secure Comms", key=f"comms_{drone['id']}"):
                    st.success(f"Secure communication activated for {drone['call_sign']}")
            with col4:
                if st.button("Full Diagnostics", key=f"diag_{drone['id']}"):
                    st.success(f"Diagnostics initiated for {drone['call_sign']}")
    
    # Telemetry charts for selected drone
    st.subheader("Detailed Telemetry Analysis")
    selected_drone = st.selectbox("Select Drone for Detailed Analysis", [d['call_sign'] for d in drones])
    
    drone = next(d for d in drones if d['call_sign'] == selected_drone)
    
    # Create telemetry charts
    fig = make_subplots(
        rows=2, cols=2,
        subplot_titles=('Altitude', 'Velocity', 'Battery Level', 'GPS Drift'),
        specs=[[{"secondary_y": False}, {"secondary_y": False}],
               [{"secondary_y": False}, {"secondary_y": False}]]
    )
    
    # Add traces
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['altitude'], name='Altitude', line=dict(color='blue')),
        row=1, col=1
    )
    
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['velocity'], name='Velocity', line=dict(color='green')),
        row=1, col=2
    )
    
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['battery'], name='Battery', line=dict(color='orange')),
        row=2, col=1
    )
    
    fig.add_trace(
        go.Scatter(x=drone['timestamps'], y=drone['gps_drift'], name='GPS Drift', line=dict(color='red')),
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
    
    fig.update_layout(height=600, showlegend=False, title_text=f"Telemetry Data for {selected_drone}")
    st.plotly_chart(fig, use_container_width=True)
    
    # Footer
    st.markdown("---")
    st.markdown("**Project Vajra Raksha** - AI for National Security | © 2023")
    st.markdown("*CLASSIFIED: FOR AUTHORIZED PERSONNEL ONLY - INDIAN ARMY USE ONLY*")

# Run the app
if not st.session_state.authenticated:
    login_form()
else:
    main_dashboard()
    if st.sidebar.button("Logout"):
        st.session_state.authenticated = False
        st.rerun()