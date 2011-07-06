<% if errorMessages %>
    <div class="error">
        <p>
            <strong><% _t('CustomHtmlFormErrorMessages.CHECK_FIELDS','Please check your input on the following fields:') %></strong>
        </p>
        <ul>
            <% control errorMessages %>
                <li>$fieldname</li>
            <% end_control %>
        </ul>
    </div>
<% end_if %>

<% if messages %>
    <div class="note">
        <ul>
            <% control messages %>
                <li>$message</li>
            <% end_control %>
        </ul>
    </div>
<% end_if %>