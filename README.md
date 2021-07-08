# Admin Low Stock Notification free extension
Admin Low Stock Notification extension allows you to manage product quantity easily from the Magento admin panel. This extension works with Multi-Source Inventory(MSI) and Multi-Store. Using this extension, you will be notified whenever a product's salable quantity goes below the required value. Low Stock Notification provides a setting to set a low stock threshold for each product.

When the user places an order and the product goes to below the threshold level, then the Admin will get a notification with low stock items.

# Main Features
- Working with the Multi-source inventory(MSI)
- Enable or disable from Magento admin
- Set notify quantity below
- Quick notification of a low stock product
- Multi-store friendly
- Customizable recipient email.

# Configuration

Admin > Store > Settings > Configuration > Landofcoder > Admin Low Stock Notification

**Configuration settings**:	
1. Enable Yes/No: To enable or disable module output. You can completely disable module by this command 
php bin/magento module:disable Lof_LowStockNotification 

2. Notify for Quantity Below:  The feature sends the email notifications to the admin, basing on the specified value.

**Email:**
1. Send email to: Input an email address, the extension will send notifications to.
2. Email Sender: Select sender of admin email
3. Email Template: It’s a default selected email template

**Output**

**Forked** from [Bharat_LowStockNotification](https://github.com/bharat2762/magento2.3.x-admin-low-stock-notification)