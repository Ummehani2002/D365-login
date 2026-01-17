<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .menu-item {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .menu-item:hover {
            background: #3498db;
            color: white;
            transform: translateY(-3px);
        }
        h3 {
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard</h1>
        <p>Welcome to the Procurement System</p>
    </div>
    
    <div class="menu-grid">
        <div class="menu-item" onclick="window.location.href='/quotations'">
            <h3>Quotation</h3>
            <p>Manage quotations</p>
        </div>
        
        <div class="menu-item" onclick="window.location.href='/purchase-requisitions'">
            <h3>Purchase Requisition</h3>
            <p>Create and track requisitions</p>
        </div>
        
        <div class="menu-item" onclick="window.location.href='/purchase-orders'">
            <h3>Purchase Order</h3>
            <p>Manage purchase orders</p>
        </div>
        
        <div class="menu-item" onclick="window.location.href='/grns'">
            <h3>Goods Receive Note</h3>
            <p>Record received goods</p>
        </div>
        
        <div class="menu-item" onclick="window.location.href='/inventory'">
            <h3>Inventory</h3>
            <p>Manage inventory items</p>
        </div>
        
        <div class="menu-item" onclick="window.location.href='/vendors'">
            <h3>Vendors/Suppliers</h3>
            <p>Manage vendor information</p>
        </div>
        
        <div class="menu-item" onclick="window.location.href='/customers'">
            <h3>Customers</h3>
            <p>Manage customer information</p>
        </div>
        
        <div class="menu-item" onclick="window.location.href='/reports'">
            <h3>Reports</h3>
            <p>View reports and analytics</p>
        </div>
    </div>
    
    <div style="margin-top: 30px; padding: 15px; background: white; border-radius: 5px;">
        <p><strong>Quick Stats:</strong></p>
        <p>Pending Quotations: 12</p>
        <p>Open Requisitions: 8</p>
        <p>Active Orders: 5</p>
        <p>Pending GRNs: 3</p>
    </div>
</body>
</html>