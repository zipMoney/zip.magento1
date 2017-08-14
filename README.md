# Magento zipMoneyPayment
## Installation
#### Install via FTP
1. Click here to download the latest package release (.tgz): https://github.com/zipMoney/magento/releases/latest
2. Extract the contents of the package to your computer
3. Upload the package contents to your Magento root directory

#### To install using from a package (Magento Connect Manager):

1. Click here to download the latest package release (.tgz): https://github.com/zipMoney/magento/releases/latest
2. Visit System > Magento Conenct > Magento Connect Manager
3. Upload the zipMoney Magento package


#### To install using [modgit](https://github.com/jreinke/modgit)

```
cd MAGENTO_ROOT
modgit init
modgit -i extension/:. add zipMoney_Magento https://github.com/zipMoney/magento
```
to update:
```
modgit update zipMoney_Magento
```

####To install using [modman](https://github.com/colinmollenhour/modman)

```
cd MAGENTO_ROOT
modman clone https://github.com/zipMoney/magento
```
to update:
```
modman update zipMoney_Magento
```

## Configuration

### Payment Section 
1. Open the Magento Admin
2. Navigate to Systems -> configuration and then locate the Payment Method section in the left menu
to access the zipMoney payment method.

![Alt text](https://static.zipmoney.com.au/github-images/m1-payment-section.png "Payment Section")

1. Set Enable  to  Yes and a   title   for the payment method  “zipMoney   – Buy   Now Pay Later”  or  “zipPay – Buy   Now Pay Later”
2. Enter the   Private Key and Public  Key.
3. Select   your    product type    (zipPay or  zipMoney)
4. Set  payment action  to  Capture, or  Authorise   if  you want    to  authorise   on  checkout    completion  and capture later
5. Set  log settings    to  Info or Debug if you want to log all the debug information as well.
6. Set  environment to  either  Sandbox (for    your    test    or  development site)   or  Production  (for    your    live    website)
7. Set  In-Context  Checkout    to  Yes to enable iframe checkout
8. Set  Sort    Order   to  0 to place the payment method on top.

### Marketing Banners and Widgets Section

1. Scroll down  and expand  Marketing   Banners and Widgets section
2. Expand   everything  and set all options to Yes/No as per your requirement.
3. Click    Save    Config  up  the top