import sys
import json

# Read the CSV file path and forecast period from command-line arguments
csv_file = sys.argv[1]
period = int(sys.argv[2])

# Load the historical sales data
with open(csv_file, 'r') as f:
    lines = f.readlines()

# Parse the CSV data (skip header)
data = []
for line in lines[1:]:  # Skip header (ds,y)
    date, sales = line.strip().split(',')
    data.append(float(sales))

# Simple Exponential Smoothing (SES) implementation
def simple_exponential_smoothing(series, alpha=0.3, forecast_period=30):
    # Initialize the smoothed value with the first data point
    smoothed = [series[0]]
    
    # Apply SES to historical data
    for t in range(1, len(series)):
        smoothed.append(alpha * series[t] + (1 - alpha) * smoothed[t-1])
    
    # Forecast future values (using the last smoothed value)
    forecasts = []
    last_smoothed = smoothed[-1]
    for _ in range(forecast_period):
        forecasts.append(last_smoothed)  # SES assumes a flat forecast based on the last smoothed value
    
    return forecasts

# Generate forecast
forecast_values = simple_exponential_smoothing(data, alpha=0.3, forecast_period=period)

# Generate dates for the forecast period
from datetime import datetime, timedelta
last_date = datetime.strptime(lines[-1].strip().split(',')[0], '%Y-%m-%d')
forecast_dates = [(last_date + timedelta(days=i+1)).strftime('%Y-%m-%d') for i in range(period)]

# Format the output as JSON
forecast_data = [{'ds': date, 'yhat': value} for date, value in zip(forecast_dates, forecast_values)]
print(json.dumps(forecast_data))