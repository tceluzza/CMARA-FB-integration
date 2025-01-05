# Wordpress-Facebook integration
Built for the [Central Mass Amateur Radio Association](https://www.cmara.org/)

## Installation Notes
1. need to set a few variables in `wp-config.php`:
  * `FACEBOOKAPI_ENCRYPTION_KEY`
  * `FACEBOOKAPI_ENCRYPTION_SALT`

  These can be generated using the [Wordpress Salt Generator](https://api.wordpress.org/secret-key/1.1/salt/) and taking the values for the `SECURE_AUTH_KEY` and `SECURE_AUTH_SALT` (or any two of the values&mdash;it shouldn't matter). Once they are defines, these values should **never** be changed.
