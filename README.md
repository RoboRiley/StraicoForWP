# StraicoForWP
## Wordpress Plugin to Integrate Straico for Post and Page Creation

This is a little hobby project created and debugged with OpenAI 01-preview model.  It has been an experiment to see how well the o1-preview model could create a Wordpress plugin. The prompts used are in the prompt history folder. The only manual coding done for this project was to make the api timeoouts a little longer. Everything else, including the integration of Parsedown, was done by 01-preview in response to the prompts.

If you want to try out the plugin, just add the .zip file as a plugin in Wordpress.
Your API key can be added in Settings > Straico Settings.

On the Straico Generator page, just select a model, give it a prompt to write a post or page, select whether it should be a post or page, and give the post or page a title.  After a minute or so it will create that post or page, save it as a draft, and take you to the edit screen for it.

This plugin includes Parsedown.php, from this project: https://github.com/erusev/parsedown -- Parsedown is released under the MIT license (same as this project).

Post comments, suggestions, and discussion on the Straico Discord please: https://discord.com/channels/1118690015443161130/1286458790316605450
