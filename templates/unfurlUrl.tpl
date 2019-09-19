{if $object->status == "SUCCESSFUL"}
	<div class="box128 unfurlCard">
		<a href="{$object->url}">
			<div></div>
			<div>
				<div class="urlTitle">{$object->title}</div>
				<div class="urlDescription">{$object->description}</div>
				<div class="urlHost">{if $object->getHost() == 'wcflabs.de' || $object->getHost() == 'wcflabs.com'}<img src="{$__wcf->getPath()}/images/unfurlUrl/wcflabs_logo_mini.svg" alt="{$object->getHost()}">{else}{$object->getHost()}{/if}</div>
			</div>
		</a>
	</div>
{else}
	<a href="{$object->url}" class="externalURL" {if EXTERNAL_LINK_REL_NOFOLLOW || EXTERNAL_LINK_TARGET_BLANK} rel="{if EXTERNAL_LINK_REL_NOFOLLOW}nofollow{/if}{if EXTERNAL_LINK_REL_NOFOLLOW && EXTERNAL_LINK_TARGET_BLANK} {/if}{if EXTERNAL_LINK_TARGET_BLANK}noopener noreferrer{/if}"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{$object->url}</a>
{/if}