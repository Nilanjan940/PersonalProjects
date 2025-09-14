
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
    .alert-box {
        background-color: #ff4b4b;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0px;
        font-weight: bold;
        text-align: center;
        animation: blink 1s infinite;
    }
    .normal-box {
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
                st.rerun()  # <--- fixed here
            else:
                st.error("Authentication failed. Please check your credentials.")

# Generate sample drone data
def generate_drone_data(num_drones=10, hours=24):
    drones = []
    for i in range(num_drones):
        drone_id = f"DRN-{1000 + i}"
        base_lat = 32.7266 + np.random.uniform(-0.5, 0.5)
        base_lon = 74.8570 + np.random.uniform(-0.5, 0.5)
        
        # Generate telemetry data
        timestamps = [datetime.now() - timedelta(hours=h) for h in range(hours, 0, -1)]
        altitude = np.random.normal(150, 30, hours)
        velocity = np.random.normal(25, 5, hours)
        battery = np.linspace(100, np.random.uniform(10, 40), hours)
        gps_drift = np.random.exponential(1, hours)
        
        # Introduce some anomalies
        anomalies = np.zeros(hours)
        if np.random.random() < 0.3:  # 30% chance of having anomalies
            anomaly_points = np.random.choice(range(5, hours-1), size=np.random.randint(1, 4), replace=False)
            for point in anomaly_points:
                anomalies[point] = 1
                altitude[point] += np.random.uniform(50, 100) * np.random.choice([-1, 1])
                velocity[point] += np.random.uniform(10, 20) * np.random.choice([-1, 1])
                gps_drift[point] += np.random.uniform(5, 15)
        
        drones.append({
            'id': drone_id,
            'call_sign': f"HAWK-{i+1}",
            'status': 'Active' if np.random.random() > 0.2 else 'Maintenance',
            'base_lat': base_lat,
            'base_lon': base_lon,
            'current_lat': base_lat + np.random.uniform(-0.01, 0.01),
            'current_lon': base_lon + np.random.uniform(-0.01, 0.01),
            'timestamps': timestamps,
            'altitude': altitude,
            'velocity': velocity,
            'battery': battery,
            'gps_drift': gps_drift,
            'anomalies': anomalies
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
    
    # Alert panel
    anomaly_drones = [d for d in drones if d['anomalies'].sum() > 0]
    if anomaly_drones:
        with st.container():
            st.markdown(f'<div class="alert-box">🚨 ALERT: Anomalies detected in {len(anomaly_drones)} drones</div>', unsafe_allow_html=True)
            for drone in anomaly_drones:
                st.write(f"- {drone['call_sign']} ({drone['id']}) - {int(drone['anomalies'].sum())} anomalies detected")
            col1, col2, col3 = st.columns(3)
            with col1:
                st.button("View Details")
            with col2:
                st.button("Initiate Countermeasures")
            with col3:
                st.button("Acknowledge Alert")
    else:
        st.markdown('<div class="normal-box">✅ SYSTEM STATUS: NORMAL</div>', unsafe_allow_html=True)
    
    # Metrics
    st.subheader("Fleet Overview")
    col1, col2, col3, col4 = st.columns(4)
    
    active_drones = len([d for d in drones if d['status'] == 'Active'])
    anomaly_count = sum([d['anomalies'].sum() for d in drones])
    avg_battery = np.mean([d['battery'][-1] for d in drones if d['status'] == 'Active'])
    
    with col1:
        st.metric("Total Drones", len(drones))
    with col2:
        st.metric("Active Drones", active_drones)
    with col3:
        st.metric("Anomalies Detected", int(anomaly_count))
    with col4:
        st.metric("Avg Battery", f"{avg_battery:.1f}%")
    
    # Map view
    st.subheader("Live Operations Map")
    
    # Create map data
    map_data = pd.DataFrame({
        'lat': [d['current_lat'] for d in drones],
        'lon': [d['current_lon'] for d in drones],
        'size': [10 for _ in drones],
        'color': ['red' if d['anomalies'].sum() > 0 else 'green' for d in drones],
        'label': [d['call_sign'] for d in drones]
    })
    
    st.map(map_data, zoom=9)
    
    # Drone status grid
    st.subheader("Drone Status")
    cols = st.columns(4)
    
    for i, drone in enumerate(drones):
        with cols[i % 4]:
            # Determine status color
            if drone['anomalies'].sum() > 0:
                status_color = "red"
            elif drone['status'] == 'Active':
                status_color = "green"
            else:
                status_color = "gray"
                
            st.markdown(f"""
            <div style='border: 2px solid {status_color}; border-radius: 10px; padding: 10px; margin: 5px;'>
                <h3 style='margin: 0;'>{drone['call_sign']}</h3>
                <p style='margin: 0;'><b>ID:</b> {drone['id']}</p>
                <p style='margin: 0;'><b>Status:</b> {drone['status']}</p>
                <p style='margin: 0;'><b>Battery:</b> {drone['battery'][-1]:.1f}%</p>
                <p style='margin: 0;'><b>Anomalies:</b> {int(drone['anomalies'].sum())}</p>
            </div>
            """, unsafe_allow_html=True)
    
    # Telemetry charts
    st.subheader("Telemetry Analysis")
    selected_drone = st.selectbox("Select Drone", [d['call_sign'] for d in drones])
    
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
    anomaly_indices = np.where(drone['anomalies'] == 1)[0]
    for idx in anomaly_indices:
        fig.add_vrect(
            x0=drone['timestamps'][idx], x1=drone['timestamps'][idx] + timedelta(minutes=30),
            fillcolor="red", opacity=0.2, line_width=0,
            row="all", col="all"
        )
    
    fig.update_layout(height=600, showlegend=False, title_text=f"Telemetry Data for {selected_drone}")
    st.plotly_chart(fig, use_container_width=True)
    
    # Anomaly analysis
    st.subheader("Anomaly Detection Details")
    
    if drone['anomalies'].sum() > 0:
        st.warning(f"Anomalies detected in {selected_drone}. Review the highlighted areas in the telemetry data.")
        
        # Show anomaly details
        for idx in anomaly_indices:
            st.write(f"**Anomaly at {drone['timestamps'][idx].strftime('%H:%M:%S')}**")
            col1, col2, col3, col4 = st.columns(4)
            with col1:
                st.metric("Altitude", f"{drone['altitude'][idx]:.1f} m", delta=f"{drone['altitude'][idx] - drone['altitude'][idx-1]:.1f}")
            with col2:
                st.metric("Velocity", f"{drone['velocity'][idx]:.1f} m/s", delta=f"{drone['velocity'][idx] - drone['velocity'][idx-1]:.1f}")
            with col3:
                st.metric("GPS Drift", f"{drone['gps_drift'][idx]:.2f}", delta=f"{drone['gps_drift'][idx] - drone['gps_drift'][idx-1]:.2f}")
            with col4:
                st.metric("Battery", f"{drone['battery'][idx]:.1f}%", delta=f"{drone['battery'][idx] - drone['battery'][idx-1]:.1f}")
    else:
        st.success(f"No anomalies detected in {selected_drone}. All systems operating normally.")
    
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
        st.rerun()  # <--- fixed here
