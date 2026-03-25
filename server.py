#!/usr/bin/env python3
"""Adeptskil Python Backend Server"""

from flask import Flask, request, jsonify, send_from_directory
from flask_cors import CORS
import sqlite3
import os

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DB_PATH = os.path.join(BASE_DIR, 'enrollments.db')

app = Flask(__name__)
CORS(app)

# ============================================================================
# DATABASE
# ============================================================================

def get_db():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn

def init_db():
    conn = get_db()
    cursor = conn.cursor()
    
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS enrollments (
            id INTEGER PRIMARY KEY,
            invoice_id TEXT UNIQUE,
            full_name TEXT,
            email TEXT,
            phone TEXT,
            company TEXT,
            city TEXT,
            pincode TEXT,
            address TEXT,
            course TEXT,
            amount REAL,
            payment_method TEXT,
            payment_status TEXT DEFAULT 'pending',
            payment_id TEXT,
            comments TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    try:
        cursor.execute('CREATE INDEX idx_email ON enrollments(email)')
        cursor.execute('CREATE INDEX idx_invoice ON enrollments(invoice_id)')
    except:
        pass
    
    conn.commit()
    conn.close()

# ============================================================================
# API ROUTES (DEFINED FIRST - More Specific)
# ============================================================================

@app.route('/api/process_enrollment', methods=['POST', 'OPTIONS'])
def process_enrollment():
    """Save enrollment to database"""
    if request.method == 'OPTIONS':
        return '', 204
    
    try:
        data = request.get_json()
        print(f"[API] POST /api/process_enrollment - {data.get('invoice_id')}")
        
        conn = get_db()
        cursor = conn.cursor()
        cursor.execute('''
            INSERT INTO enrollments (
                invoice_id, full_name, email, phone, company, city, pincode, address,
                course, amount, payment_method, payment_status, payment_id, comments
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ''', (
            data.get('invoice_id'),
            data.get('full_name'),
            data.get('email'),
            data.get('phone'),
            data.get('company', ''),
            data.get('city', ''),
            data.get('pincode', ''),
            data.get('address', ''),
            data.get('course'),
            data.get('amount'),
            data.get('payment_method'),
            data.get('payment_status', 'pending'),
            data.get('payment_id', ''),
            data.get('comments', '')
        ))
        conn.commit()
        conn.close()
        
        print("[DB] ✓ Saved")
        return jsonify({'success': True}), 200
    except sqlite3.IntegrityError as e:
        return jsonify({'error': f'Duplicate: {str(e)}'}), 400
    except Exception as e:
        print(f"[ERROR] {str(e)}")
        return jsonify({'error': str(e)}), 500

@app.route('/api/get_enrollments', methods=['GET', 'OPTIONS'])
def get_enrollments():
    """Fetch all enrollments"""
    if request.method == 'OPTIONS':
        return '', 204
    
    try:
        conn = get_db()
        cursor = conn.cursor()
        cursor.execute('SELECT * FROM enrollments ORDER BY created_at DESC LIMIT 1000')
        rows = cursor.fetchall()
        conn.close()
        enrollments = [dict(row) for row in rows]
        print(f"[API] GET /api/get_enrollments - {len(enrollments)} records")
        return jsonify({'enrollments': enrollments}), 200
    except Exception as e:
        print(f"[ERROR] {str(e)}")
        return jsonify({'error': str(e)}), 500

@app.route('/api/download_database', methods=['GET'])
def download_database():
    """Download database file"""
    try:
        if os.path.exists(DB_PATH):
            print(f"[API] Downloading database")
            return send_from_directory(BASE_DIR, 'enrollments.db', as_attachment=True)
        return jsonify({'error': 'Not found'}), 404
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# ============================================================================
# STATIC FILE ROUTES (DEFINED LAST - Catch-All)
# ============================================================================

@app.route('/')
def index():
    """Serve root index.html"""
    return send_from_directory(BASE_DIR, 'index.html')

@app.route('/<path:filepath>', methods=['GET'])
def serve_static(filepath):
    """Serve all other static files"""
    try:
        # Never serve api paths here - they should be handled by routes above
        if filepath.startswith('api'):
            return jsonify({'error': 'Not found'}), 404
        
        # Security check
        if '..' in filepath:
            return jsonify({'error': 'Forbidden'}), 403
        
        # Build full path
        full_path = os.path.join(BASE_DIR, filepath)
        
        # Check if exists
        if not os.path.exists(full_path):
            print(f"[404] File not found: {filepath}")
            return jsonify({'error': 'Not found'}), 404
        
        # If directory, serve index.html
        if os.path.isdir(full_path):
            index_path = os.path.join(full_path, 'index.html')
            if os.path.exists(index_path):
                return send_from_directory(full_path, 'index.html')
            return jsonify({'error': 'Forbidden'}), 403
        
        # Serve the file
        return send_from_directory(BASE_DIR, filepath)
    
    except Exception as e:
        print(f"[ERROR] Serving {filepath}: {str(e)}")
        return jsonify({'error': 'Error'}), 500

# ============================================================================
# ERROR HANDLERS
# ============================================================================

@app.errorhandler(404)
def not_found(e):
    return jsonify({'error': 'Not found'}), 404

@app.errorhandler(405)
def method_not_allowed(e):
    return jsonify({'error': 'Method not allowed'}), 405

# ============================================================================
# MAIN
# ============================================================================

if __name__ == '__main__':
    init_db()
    print("="*70)
    print("Adeptskil Python Server")
    print("="*70)
    print("✓ Database initialized: " + DB_PATH)
    print("✓ API routes active:")
    print("  - POST   /api/process_enrollment")
    print("  - GET    /api/get_enrollments")
    print("  - GET    /api/download_database")
    print("="*70)
    print("Opening: http://localhost:8000")
    print("Press Ctrl+C to stop")
    print("="*70)
    app.run(host='0.0.0.0', port=8000, debug=False)
