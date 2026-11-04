# Contact page

1. Activate Contact Form 7 and create a form under **Contact → Contact Forms**. Configure the destination in its **Mail** tab.
2. Create or open the Contact Page and assign the **Contact** template. Save and reopen it to edit the introduction and public contact details through Secure Custom Fields. Set a featured image to display it in the page header.
3. In **Appearance → Customise → Contact**, choose the form in **Enquiry form** and publish the settings.
4. Once the page is ready, point the homepage contact button to its URL.

Use the following as a starting point for the CF7 form. Update the Mail template to use the same field names.

```text
<label>Name [text* your-name autocomplete:name]</label>
<label>Email [email* your-email autocomplete:email]</label>
<label>Message [textarea* your-message]</label>
[submit class:button "Send enquiry"]
```

The public email field is displayed on the page. The form recipient is managed separately in CF7. If the form becomes unavailable, the template keeps displaying the configured contact details. An empty introduction falls back to the existing Page body.

On staging, check the page at mobile and desktop widths, tab through the form, trigger a validation error and submit to a test inbox. Confirm both the success message and receipt of the email. Check that the alternate contact details remain available with the form unselected.
