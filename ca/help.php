<?php

include('../config.php');
include(STORE_DIR.'/config/config.php');
include('../include/my_functions.php');
include('../include/common.php');

printHeader('ca');

?>
<p>
<div style="text-align:center">
<br>
<br>

<table class="menu" style="width:60%">
<tr><th class="menu"><h2>PHPki HELP FILE<br>TABLE OF CONTENTS</h2></th><tr>
<td class="menu" style="padding: 1em;font-weight:bold">
<a href="#WHY">&raquo; Why PHPki</a><br>
<a href="#OVERVIEW">&raquo; Overview</a><br>
<a href="#MAIN-MENU">&raquo; Main Menu</a><br>
<a href="#REQUEST-FORM">&raquo; Requesting a New Certificate</a><br>
<a href="#MANAGE">&raquo; Managing Your Certificate With The Control Panel</a><br>
<a href="#REVOKE">&raquo; Revoking a Certificate</a><br>
<a href="#DISPLAY">&raquo; Displaying Certificate Details</a><br>
<a href="#RENEW">&raquo; Renewing a Cettificate</a><br>
<a href="#DOWNLOAD">&raquo; Downloading a Certificate</a><br>
<a href="../help.php" target="help">&raquo; End User Help Documents</a><br>
<a href="#GLOSSARY">&raquo; The PHPki Glossary of Terms</a><br>
<a href="#GETTING-HELP">&raquo; Getting Additional Help</a><br>
</td></tr>
</table>
</div>

<br><br>

<p>
<h2><a id="WHY">WHY PHPki</a></h2>
<blockquote>
PHPki is an <a href="http://www.opensource.org" target="_blank">Open Source</a>
Web based application for managing a 
<a href="../help/glossary.html#PKI" target="glossary"> "Public Key Infrastructure"</a> 
within a small organization.  
PHPki may be used to create and manage 
<a href="../help/glossary.html#CERTIFICATE" target="glossary">digital certificates</a> 
for use with <a href="../help/glossary.html#SMIME" target="glossary">S/MIME</a> enabled 
e-mail clients, SSL servers, and VPN applications. 

<p>
Most commercial 
<a href="../help/glossary.html#CA" target="glossary">certificate authorities (CA)</a> 
require that certificates be issued to individual workstations, one at a time.  
The transaction required to obtain a commercial certificate must usually take 
place at the workstation on which the certifcate is to be installed, and can be
complicated, confusing, and time consuming.  Such a process does not allow for
easy centralized administration of groups of certificates, where a single
person within an organization or department must request, create, and install
certificates on a number of workstations.

<p>
PHPki creates standard <a href="../help/glossary.html#X509" target="glossary">X.509</a> 
digital certificates which should work with most e-mail clients.  
It packages private certificates in the <a href="../help/glossary.html#PKCS12" target="glossary">PKCS#12</a> format accepted by Microsoft 
e-mail clients <a href="../help/glossary.html#PEM" target="glossary">PEM</a> used by certain web servers.  
PKCS #12 certificates usually have a <cite>.P12</cite> 
filename extension.  Since most PKCS #12 certificates usually include the 
certificate's private key, they should never be distributed to the general 
public. PHPki's publicly distributable certificates are packaged in
standard <a href="../help/glossary.html#DER" target="glossary">DER</a> format.

<p>
Server 

</blockquote>

<p>
<h2><a id="OVERVIEW">OVERVIEW</a></h2>
<blockquote>
The process of creating and using digital certificates with PHPki is 
fairly easy.
<ul>
<li>
First you must download and install our
<a href="../help/glossary.html#ROOT-CERT" target="glossary">root certificate</a> 
on your computer.  Everyone else you intend to exchange encrypted e-mail
with must also install our root certificate. 
 Everyone who installs our root certificate becomes a member of our
"circle of trust".  The PHPki main menu contains an option for downloading
our root certificate.  Root certificates are not private and should be widely
distributed and published on the Internet in a conspicuous location.  
The more widely published a root certificate is, the more difficult it becomes
to forge.
</li>


<p><li>
You must request and download a digital certificate for each person who will
<strong>RECEIVE</strong> encrypted e-mail at your agency.   Remember, having a
digital certificate does not enable one to <strong>send</strong> encrypted 
e-mail, but only to <strong>receive</strong> it.  Each of the certificates
you download must be installed on the respective users' workstations.  
If you wish to send encrypted e-mail to someone, you must install that person's
public certificate on your computer.  You can obtain another person's public
certificate simply by having them send you a 
<a href="../help/glossary.html#SIGNATURE" target="glossary">digitally signed</a> e-mail message.  When you receive the message, your e-mail
program should give you the option to add the sender's public key to your
address book or key ring.  Once you have installed your digital certificates,
your users should send digitally signed messages to each person who will need
to send encrypted e-mail to them.
</li>

<p><li>
Users come and go, passwords are compromised, and files are lost, such is life.
PHPki includes a certificate management system for handling these situations.
The certificate management control panel gives you the ability to
display certificates in excruciating detail, revoke a certificate when its
e-mail address is no longer valid or its public key has been compromised, 
renew certificates which have or will expire, and re-download a previously
issued certificate if you've lost the original.
</li>

<p><li>
There must be a method for letting outside entities know which of your
certificates have been revoked.  The mechanism for doing this is the 
<cite>Certificate Revocation List</cite> or CRL.  A CRL is a digitally signed
list of certificates which have been revoked by a Certificate Authority.
Our CRL is updated periodically, and can be downloaded from the PHPki
Main Menu. Many e-mail clients will automatically download and install CRLs
using information embedded in certificates.  However, there is no widely
adopted standard for automatic CRL checking, so it is not unusual to have
to manually install and update CRLs.
</li>

<p><li>
PHPki provides a public interface for Internet users to download our root
certificate and certificate revokation list.  A certificate search feature
is also provided to allow easy distribution of public certificates over the
Internet.
</li>
</ul>
</blockquote>

<p>
<h2><a id=MAIN-MENU>THE MAIN MENU</a></h2>
<p>
<blockquote>
<div style="text-align:center"><img src="../images/main-menu.png" width="700px" ></div>
<p>
All of the PHPki primary functions can be accessed from the Main Menu.  
It is possible to navigate back to the Main Menu from any screen by clicking the
"Menu" link in the upper right corner of each page.  Clicking the <cite>Public</cite> link will open a new browser window to the public content menu where
the general public may search for certificates and download the
<a href="../help/glossary.html#ROOT-CERT" target="glossary">Root Certificate</a> and <a href="../help/glossary.html#CRL" target="glossary">Certificate Revocation List.</a>
</blockquote>

<p>
<h2><a id=REQUEST-FORM>REQUESTING A NEW CERTIFICATE</a></h2>
<blockquote>
When you select "Request a New Certificate" from the Main Menu, you will be
presented with the Certificate Request Form.<br>
<p>
<div style="text-align:center"><img src="../images/cert-request-form.png" width="700px" ></div>
<p>
This form is used to collect the minimum necessary information required to
issued a new digital certificate. All fields must be completed.
<blockquote><ul>
<li>
<b>E-mail User's Full Name:</b> Enter the full name of the user for which the certificate will be issued.  
</li>
<p><li>
<b>E-mail Address:</b> Enter the e-mail address of the user for which the certificate is to be issued.  This field will be checked for proper e-mail address
format, but the e-mail address is not verified otherwise.
</li>
<p><li>
<b>Organization:</b> Enter the full name of your organization (i.e. ACME Shoe Repair).
</li>
<p><li>
<b>Department/Unit:</b> Enter the name of the department or unit in which the 
user works. (i.e. Accounting Department).
</li>
<p><li>
<b>Locality:</b> Enter the name of the City or County in which the organization
is located.
</li>
<p><li>
<b>State/Province:</b> Enter the name of the State or Province in which the organization
is located.
</li>
<p><li>
<b>Country:</b> Enter the name of the Country in which the organization
is located.
</li>
<p><li>
<b>Certificate Password:</b> Enter a password to protect the certificate.  
If you enter a password, it must ben enter twice for verification.  
This password will be used to encrypt the private key which will be packaged
with the completed certificate.  It may also be required when installing a PKCS#12
certificate. <strong>This password should be handled with the
utmost security and should never be lost, as it cannot be recovered under 
any circumstance.</strong> If this password is lost, you must immediately
revoke the certificate and request/create a new certificate for the user.
</li>
<p><li>
<b>Certificate Life:</b> Select the number of years you want the certificate to
be valid.  Although it is common practice to issue certificates which are valid
for only one year, the option to issue certificates for a longer period is 
available should you wish to be rebel. The certificate may be revoked or
renewed at any point during its life.
</li>
<p><li>
<b>Key Size:</b> Select this size of your private key in bits.  Larger
keys are considered more secure.  However, certain VPN applications may
have difficulty with keys larger than 1024 bits.
</li>
<p><li>
<b>Certificate Use:</b> Select the purpose for which the certificate will
be use.  E-mail certifcates have different attributes from SSL server
certifcates and may not be interchangeable.  Some IPSEC/VPN applications 
may be sensitive to large certificates, so those certificates contain less
embedded information to keep them small.
</li>
</ul></blockquote>

<p>
When you have complete filling in the form, click the "Submit Request" button.
The information you submitted will be checked for errors, and a confirmation
screen will be displayed.
<p>
<div style="text-align:center"><img src="../images/request-confirm-form.png" width="700px"></div>
<p>
Clicking the "Yes! Create and Download" button will cause a file download
window to open in your browser, allowing you to save the certificate on your
computer under whatever name you choose.  The default name for each certificate
is derived from the e-mail address provided in the certificate request form.
You may download the certificate as many time as you wish as long as your
browser remains on this page.  If you navigate from this page, you will have
to use the <cite>Certificate Management Control Panel</cite> to download the certificate
again.  Be sure to save all of your certificates in a safe and secure
place.  Doing so will make it easier for you to re-install a certificate on a
user's workstation should the need arise.<br>
<p>
After the download window closes, you may click the "Back" button to return
to the form and request another certificate.  All of the data you previously
entered will be retained.  This is to allow you to issue a large number of
certificates without having to re-enter much of the form.  As well, your
form input will be saved as your default values for the future sessions<br>
</blockquote>


<p>
<h2><a id="MANAGE">MANAGING YOUR CERTIFICATES WITH THE CONTROL PANEL</a></h2>
<blockquote>
PHPki provides one convenient place to manage your certificates.  
It is called the <cite>Certificate Management Control Panel</cite>.
<p>
<div style="text-align:center"><img src="../images/ctrl-panel-before.png" width="700px"></div>
<p>
With the <cite>Control Panel</cite> you can display, download, revoke, and 
renew your certificates by simply clicking on the appropriate button to the 
right of each certificate entry. Your certificates are listed in columnar 
format, with the left-most color coded "Status" column showing whether a 
certificate is "<span style="font-color:green">Valid</span>" or 
"<span style="font-color:red">Revoked</span>".  The listing can be sorted in any order
by clicking on the column headings.  An arrow graphic 
&nbsp;<img src="../images/uparrow-blue.gif" height="12px">&nbsp; beside a column heading
indicates which column is being used to sort the listing.  Clicking on the 
arrow graphic will cause the listing to alternate between ascending and 
descending sort order. You may find these sort features particularly useful if
you are careful to plan and utilize the <cite>Department/Unit</cite> and 
<cite>Locality</cite> fields to categorize your certificates according to 
your particular organizational needs.
</blockquote>

<p>
<h2><a id="REVOKE">REVOKING A CERTIFICATE</a></h2>
<blockquote>
At times it may become necessary to revoke or invalidate a certificate.  This
usually happens when an e-mail address is no longer valid, or the certificate's
private key has been lost or compromised.  
<p>
To revoke a certificate, click on the <img src="../images/revoke.png" align="top">&nbsp; icon next to the certificate entry in the <cite>Control Panel</cite>.
<p>
<div style="text-align:center"><img src="../images/revoke-confirm.png" width="700px"></div>
<p>
You will then be asked to confirm or cancel the revocation.  Be absolutely
sure of what you wish to do before clicking the "Yes" button.  Once a
certificate is revoked, it cannot be un-revoked.  Well, this isn't completely
true, as a revoked certificate can be renewed.  Renewing a revoked certificate
results in a <strong>new</strong> certificate being issued.  Certificate
renewal is covererd later.
<p>
<div style="text-align:center"><img src="../images/ctrl-panel-after-revoke.png" width="700px"></div>
<p>
If you click the "Yes" button, the certificate is revoked with no further
interaction.  The certificate's status in the <cite>Control Panel</cite> 
will change to <span style="font-color:red">Revoked</span>.
</blockquote>

<p>
<h2><a id="DISPLAY">DISPLAYING CERTIFICATE DETAILS</a></h2>
<blockquote>
Certificates may be displayed in full detail by clicking the
<img src="../images/display.png" align="top">&nbsp; icon next to a certificate's 
entry in the <cite>Control Panel</cite>.  Although some users may find this
feature useful, many will not find anything of interest in it.
<p>
<div style="text-align:center"><img src="../images/display-revoked.png" width="700px"></div>
</blockquote>

<p>
<h2><a id="RENEW">RENEWING A CERTIFICATE</a></h2>
<blockquote>
Certificates expire periodically.  The usually length a time for which a
certificate is valid is one year.  With PHPki, you have the option to
issue certificates with a more extended life span.  Regardless, sooner or later
your certificates will begin to expire.  
<p>To renew a certificate which has expired or is near expiration, simply click
the <img src="../images/renew.png" align="top">&nbsp; icon next to the
certifcate's <cite>Control Panel</cite> entry.  You will then be presented
with a certificate renewal form.
<p>
<div style="text-align:center"><img src="../images/renewal-form.png" width="700px"></div>
<p>
The certificate renewal form takes the values for <cite>Common Name, 
E-mail Address, Organization, etc.</cite> from the original certificate.
Those fields are disabled in the form, and cannot be changed.  
You are required to enter the original certificate's password and select
a life span for the new certificate.  If you do not enter the correct
password that was assigned to the original certificate when it was created,
you will not be able to renew the certificate.  You may cancel this operation
by clicking the "Back" button, which will take you back to the 
<cite>Control Panel</cite>.
<p>
<div style="text-align:center"><img src="../images/ctrl-panel-after-renew.png" width="700px"></div>
<p>
If you click the "Submit Request" button to renew the certificate, it is
renewed with no further interaction, and you will be returned to the
<cite>Control Panel</cite>.  You will notice a new 
<span style="font-color:green">Valid</span> certificate in the <cite>Control Panel</cite>, 
and the old expired certificate is marked <span style="font-color:red">Revoked</span>.
</blockquote>

<p>
<h2><a id=DOWNLOAD>DOWNLOADING A CERTIFICATE</a></h2>
<blockquote>
If you lose the original file you downloaded when you first created a
certificate, you may download another copy of a certificate at any time by
clicking the
<img src="../images/download.png" align="top">&nbsp; icon next to the certificate's entry
in the <cite>Control Panel</cite>. When downloading a certificate, you will
be reminded that the certificate is a
<b style="color:red">PRIVATE</b> certificate, which <b style="color:red">
SHOULD NEVER BE DISTRIBUTED TO THE PUBLIC</b>. 
You may choose to download <a href="../help/glossary.html#PKCS12" target="glossary">PKCS #12</a> or <a href="../help/glossary.html#PEM" target="glossary">PEM</a> formatted bundles.
<p>
<div style="text-align:center"><img src=../images/confirm-download.png width="700px"></div>
</blockquote>

<p>
<h2><a id=GLOSSARY>GLOSSARY</a></h2>
<blockquote>
Click <a href="../help/glossary.html#TOP" target="glossary">here</a> to view the complete
PHPki glossary of terms.
</blockquote>


<p>
<h2><a id="GETTING-HELP">GETTING ADDITIONAL HELP</a></h2>
<blockquote>
<?php print $config['getting_help']?>
</blockquote>
<br>

<?php
printFooter();
?>
