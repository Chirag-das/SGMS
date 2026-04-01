# Biometric API Documentation
## Security Guard Management System (SGMS) v1.0

**API Endpoint:** `/api/biometric.php`

---

## Overview

The Biometric API allows external biometric devices (fingerprint, RFID, face recognition, etc.) to integrate with SGMS. The API automatically processes attendance records when devices send employee check-in/check-out events.

**Protocol:** HTTP POST  
**Content-Type:** application/json  
**Authentication:** Optional (can be added via X-API-Key header)  
**Response Format:** JSON

---

## Endpoint Details

### URL
```
POST http://[your-domain]/Security-Guard-Management-System/api/biometric.php
```

### Example URL
```
http://localhost/Security-Guard-Management-System/api/biometric.php
```

---

## Request Format

### Required Headers
```
Content-Type: application/json
```

### Request Body (JSON)
```json
{
  "device_id": "string (required)",
  "employee_id": "string (required)",
  "timestamp": "string (required)",
  "event_type": "string (required)",
  "temperature": "float (optional)",
  "location": "string (optional)"
}
```

### Field Descriptions

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `device_id` | String | Yes | Unique identifier of biometric device | "BIO-001" |
| `employee_id` | String | Yes | 10-character employee ID from SGMS | "G202100001" |
| `timestamp` | String | Yes | Event timestamp (YYYY-MM-DD HH:MM:SS format) | "2026-02-17 09:00:00" |
| `event_type` | String | Yes | Either "check_in" or "check_out" | "check_in" |
| `temperature` | Float | No | Employee body temperature (for health screening) | 36.5 |
| `location` | String | No | Physical location of device | "Main Gate" |

---

## Request Examples

### Example 1: Simple Check-In
```bash
curl -X POST http://localhost/Security-Guard-Management-System/api/biometric.php \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "BIO-001",
    "employee_id": "G202100001",
    "timestamp": "2026-02-17 09:00:00",
    "event_type": "check_in"
  }'
```

### Example 2: Check-Out with Temperature
```bash
curl -X POST http://localhost/Security-Guard-Management-System/api/biometric.php \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "BIO-001",
    "employee_id": "G202100001",
    "timestamp": "2026-02-17 17:30:00",
    "event_type": "check_out",
    "temperature": 36.5,
    "location": "Main Gate"
  }'
```

### Example 3: Using PowerShell (Windows)
```powershell
$url = "http://localhost/Security-Guard-Management-System/api/biometric.php"
$body = @{
    device_id = "BIO-001"
    employee_id = "G202100001"
    timestamp = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
    event_type = "check_in"
}
$json = $body | ConvertTo-Json
Invoke-RestMethod -Uri $url -Method POST -ContentType "application/json" -Body $json
```

### Example 4: Using Python
```python
import requests
import json
from datetime import datetime

url = "http://localhost/Security-Guard-Management-System/api/biometric.php"
data = {
    "device_id": "BIO-001",
    "employee_id": "G202100001",
    "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
    "event_type": "check_in"
}

response = requests.post(url, json=data)
print(response.status_code)
print(response.json())
```

### Example 5: Using JavaScript (Node.js)
```javascript
const fetch = require('node-fetch');

const data = {
  device_id: 'BIO-001',
  employee_id: 'G202100001',
  timestamp: new Date().toISOString().slice(0, 19).replace('T', ' '),
  event_type: 'check_in'
};

fetch('http://localhost/Security-Guard-Management-System/api/biometric.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

---

## Response Format

### Success Response (HTTP 200 or 201)
```json
{
  "success": true,
  "message": "Attendance updated successfully",
  "code": 200,
  "data": {
    "attendance_id": 125,
    "guard_id": 5,
    "employee_id": "G202100001",
    "date": "2026-02-17",
    "check_in_time": "09:00:00",
    "check_out_time": null,
    "status": "present",
    "hours_worked": null,
    "overtime_hours": 0,
    "remarks": "Checked in from device BIO-001"
  }
}
```

### Error Response (HTTP 4xx or 5xx)
```json
{
  "success": false,
  "message": "Error description",
  "code": 400,
  "error": "Detailed error information"
}
```

---

## HTTP Status Codes

| Code | Meaning | Scenario |
|------|---------|----------|
| 200 | OK | Attendance updated successfully |
| 201 | Created | New attendance record created |
| 400 | Bad Request | Missing required fields or invalid format |
| 404 | Not Found | Employee ID not found in system |
| 405 | Method Not Allowed | Request method is not POST |
| 500 | Server Error | Database or system error |

---

## Response Examples

### Example 1: Check-In Success
```json
{
  "success": true,
  "message": "Attendance updated successfully",
  "code": 200,
  "data": {
    "attendance_id": 125,
    "guard_id": 5,
    "employee_id": "G202100001",
    "date": "2026-02-17",
    "check_in_time": "09:00:00",
    "check_out_time": null,
    "status": "present",
    "hours_worked": null
  }
}
```

### Example 2: Check-Out Success
```json
{
  "success": true,
  "message": "Attendance updated successfully",
  "code": 200,
  "data": {
    "attendance_id": 125,
    "guard_id": 5,
    "employee_id": "G202100001",
    "date": "2026-02-17",
    "check_in_time": "09:00:00",
    "check_out_time": "17:30:00",
    "status": "present",
    "hours_worked": 8.5,
    "overtime_hours": 0.5
  }
}
```

### Example 3: Employee Not Found
```json
{
  "success": false,
  "message": "Employee not found",
  "code": 404,
  "error": "No guard found with employee ID: G202199999"
}
```

### Example 4: Missing Required Field
```json
{
  "success": false,
  "message": "Validation error",
  "code": 400,
  "error": "Missing required field: timestamp"
}
```

---

## Business Logic

### Check-In Process
1. Employer scans at device with employee ID: G202100001
2. Device sends POST request with `event_type: "check_in"`
3. System finds guard by employee_id
4. System records `check_in_time` in attendance table
5. System sets `status` to "present"
6. Returns success response with attendance_id

### Check-Out Process
1. Employer scans at device again with same employee ID
2. Device sends POST request with `event_type: "check_out"`
3. System finds today's attendance record for this guard
4. System records `check_out_time`
5. **System auto-calculates:**
   - `hours_worked` = difference between check_out_time and check_in_time
   - `overtime_hours` = hours_worked - 8 (if > 8)
   - `status` remains "present"
6. Returns success response with calculated hours

### Same-Day Multiple Events
- **If device sends check_in when already checked-in:** Updates the existing check_in_time
- **If device sends check_out when not checked-in:** Creates new record with only check_out_time
- **If device sends check_out when already checked-out:** Updates the existing check_out_time

---

## Employee ID Format

Employee IDs in SGMS follow the format: **G[YYMMDD][####]**

Example: `G202100001`
- **G** = Guard prefix
- **202100** = Year 2021, Month 01
- **0001** = Sequential ID

To find employee IDs in the system:
1. Login to SGMS as admin
2. Go to Guards management
3. Employee IDs are displayed in the table
4. Can also configure biometric device with employee IDs from database

---

## Device Integration Examples

### Configuration A: Standalone Biometric Device
- Device Model: ZKTeco, Essl, or similar
- Configuration: IP address, endpoint URL
- Frequency: Real-time on each scan
- Advantage: Immediate attendance logging

### Configuration B: Batch Processing
- Device Model: Any device with CSV export
- Configuration: Export data periodically
- Process: Parse CSV and send to API in bulk
- Advantage: Can handle multiple records

### Configuration C: API Gateway
- Setup: Intermediate server processing
- Process: Device → Gateway → SGMS API
- Benefit: Additional validation and security

---

## Testing the API

### Using Curl (Command Line)
```bash
# Test endpoint with actual data
curl -X POST http://localhost/Security-Guard-Management-System/api/biometric.php \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "TEST-DEVICE",
    "employee_id": "G202100001",
    "timestamp": "2026-02-17 10:00:00",
    "event_type": "check_in"
  }' \
  -v
```

### Using Postman (GUI Tool)
1. Open Postman
2. Create new POST request
3. URL: `http://localhost/Security-Guard-Management-System/api/biometric.php`
4. Headers: `Content-Type: application/json`
5. Body: Select "raw" → "JSON" 
6. Paste JSON payload
7. Click "Send"
8. View response

### Using test.php Health Check
1. Visit: `http://localhost/Security-Guard-Management-System/test.php`
2. Scroll to API section
3. Click "Test API" if available
4. Or use curl examples above

---

## Error Handling

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| "Invalid JSON" | Malformed JSON in request | Validate JSON syntax |
| "Missing required field" | A required field is missing | Include all required fields |
| "Employee not found" | Employee ID doesn't exist | Verify employee ID in SGMS |
| "Invalid event_type" | event_type is not "check_in" or "check_out" | Use correct event type |
| "Database error" | Connection or query issue | Check MySQL is running |
| "Invalid timestamp format" | Timestamp not YYYY-MM-DD HH:MM:SS | Use correct format |

### Error Response Codes
```
400: Bad Request - Fix your request format
404: Not Found - Employee ID doesn't exist  
405: Method Not Allowed - Use POST method
500: Server Error - Check server logs
```

---

## Security Considerations

### For Production Deployment

1. **Authentication**
   ```
   Add X-API-Key header for device authentication
   ```

2. **HTTPS**
   ```
   Always use HTTPS for API calls in production
   Configure in .htaccess file
   ```

3. **Rate Limiting**
   ```
   Implement rate limiting to prevent abuse
   Typically: 1000 requests per minute per device
   ```

4. **Logging**
   ```
   All API calls are logged in audit_logs table
   Review logs regularly for anomalies
   ```

5. **IP Whitelisting**
   ```
   Restrict API access to known device IPs
   Configure in firewall/router
   ```

6. **Data Validation**
   ```
   API validates all inputs
   Prevents SQL injection
   Sanitizes all incoming data
   ```

---

## Bulk Import Example

### Importing Multiple Attendance Records

```bash
#!/bin/bash
# Bash script to import attendance records from CSV file

CSV_FILE="attendance.csv"
API_URL="http://localhost/Security-Guard-Management-System/api/biometric.php"

# Read CSV file (format: device_id,employee_id,timestamp,event_type)
while IFS=',' read -r device_id employee_id timestamp event_type; do
    DATA="{\"device_id\":\"$device_id\",\"employee_id\":\"$employee_id\",\"timestamp\":\"$timestamp\",\"event_type\":\"$event_type\"}"
    
    RESPONSE=$(curl -s -X POST "$API_URL" \
      -H "Content-Type: application/json" \
      -d "$DATA")
    
    echo "Imported: $employee_id - Response: $RESPONSE"
    
    # Add small delay to avoid overwhelming server
    sleep 0.5
    
done < "$CSV_FILE"
```

---

## Database Schema

### Attendance Table Structure
```sql
CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  guard_id INT NOT NULL,
  date DATE NOT NULL,
  check_in_time TIME,
  check_out_time TIME,
  status ENUM('present', 'absent', 'leave') DEFAULT 'present',
  hours_worked FLOAT DEFAULT 0,
  overtime_hours FLOAT DEFAULT 0,
  remarks TEXT,
  created_by INT,
  updated_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (guard_id) REFERENCES guards(id) ON DELETE CASCADE
);
```

### Guards Table Reference
```sql
-- To find guards:
SELECT id, employee_id, full_name, phone 
FROM guards 
WHERE status = 'active';
```

---

## Integration Checklist

- [ ] Employee IDs in biometric device match SGMS guards table
- [ ] Device supports JSON POST requests
- [ ] Network connectivity between device and server verified
- [ ] Correct API endpoint URL configured in device
- [ ] Sample test request successful
- [ ] Check-in and check-out events both working
- [ ] Attendance records appearing in SGMS dashboard
- [ ] Hours worked calculated correctly
- [ ] Multiple devices tested (if applicable)
- [ ] Error handling verified
- [ ] Logging and audit trail working

---

## Support & Troubleshooting

### API Not Responding
```
1. Verify PHP is running
2. Check api/biometric.php file exists
3. Verify URL is correct
4. Check network connectivity
5. Review server logs
```

### Employees Not Found
```
1. Verify employee_id format: G[YYMMDD][####]
2. Check employee exists in guards table
3. Verify employee status is 'active'
4. Check spelling of employee_id
```

### Hours Not Calculating
```
1. Ensure both check_in and check_out are sent
2. Verify timestamp format is YYYY-MM-DD HH:MM:SS
3. Check that times are on same day
4. Review attendance record in database
```

### Database Connection Error
```
1. Verify MySQL is running
2. Check database credentials in config/database.php
3. Verify sgms_db database exists
4. Run phpMyAdmin to test connection
```

---

## Performance Optimization

### For High-Volume Environments

1. **Batch API Calls**
   - Send multiple events in single transaction
   - Reduce network overhead

2. **Caching**
   - Cache employee lookups
   - Reduce database queries

3. **Async Processing**
   - Use message queues for high volume
   - Process asynchronously

4. **Database Indexing**
   - Ensure indexes on guard_id, employee_id
   - Check query performance

---

## Future Enhancements

- [ ] Multi-device synchronization
- [ ] Attendance approval workflow
- [ ] Mobile app sync
- [ ] Real-time dashboard updates
- [ ] Advanced anomaly detection
- [ ] Predictive attendance analytics
- [ ] Integration with payroll systems

---

## Support Contact

For API integration support:
1. Check this documentation
2. Review SETUP.md for basic configuration
3. Test using provided curl examples
4. Review application logs for errors

---

**API Version:** 1.0  
**Last Updated:** February 2026  
**Status:** Production Ready ✓

