<!--
	- @copyright 2022 Christopher Ng <chrng8@gmail.com>
	-
	- @author Christopher Ng <chrng8@gmail.com>
	-
	- @license AGPL-3.0-or-later
	-
	- This program is free software: you can redistribute it and/or modify
	- it under the terms of the GNU Affero General Public License as
	- published by the Free Software Foundation, either version 3 of the
	- License, or (at your option) any later version.
	-
	- This program is distributed in the hope that it will be useful,
	- but WITHOUT ANY WARRANTY; without even the implied warranty of
	- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	- GNU Affero General Public License for more details.
	-
	- You should have received a copy of the GNU Affero General Public License
	- along with this program. If not, see <http://www.gnu.org/licenses/>.
	-
-->

<template>
	<div v-if="isEditable" class="property">
		<textarea v-if="multiLine"
			:id="name"
			:placeholder="placeholder"
			:value="value"
			rows="8"
			autocapitalize="none"
			autocomplete="off"
			autocorrect="off"
			@input="onPropertyChange" />
		<input v-else
			:id="name"
			type="text"
			:placeholder="placeholder"
			:value="value"
			autocapitalize="none"
			autocomplete="on"
			autocorrect="off"
			@input="onPropertyChange">

		<div class="property__actions-container">
			<transition name="fade">
				<Check v-if="showCheckmarkIcon" :size="20" />
				<AlertOctagon v-else-if="showErrorIcon" :size="20" />
			</transition>
		</div>
	</div>
	<span v-else>
		{{ value || t('settings', 'No {property} set', { property: readable.toLocaleLowerCase() }) }}
	</span>
</template>

<script>
import debounce from 'debounce'
import { showError } from '@nextcloud/dialogs'

import Check from 'vue-material-design-icons/Check'
import AlertOctagon from 'vue-material-design-icons/AlertOctagon'

import { savePrimaryAccountProperty } from '../../../service/PersonalInfo/PersonalInfoService.js'
import logger from '../../../logger.js'

export default {
	name: 'AccountPropertyInput',

	components: {
		AlertOctagon,
		Check,
	},

	props: {
		name: {
			type: String,
			required: true,
		},
		value: {
			type: String,
			required: true,
		},
		scope: {
			type: String,
			required: true,
		},
		readable: {
			type: String,
			required: true,
		},
		isEditable: {
			type: Boolean,
			default: true,
		},
		multiLine: {
			type: Boolean,
			default: false,
		},
		placeholder: {
			type: String,
			required: true,
		},
		emptyStringValid: {
			type: Boolean,
			default: true,
		},
		onSave: {
			type: Function,
			default: null,
		},
	},

	data() {
		return {
			initialValue: this.value,
			localScope: this.scope,
			showCheckmarkIcon: false,
			showErrorIcon: false,
		}
	},

	methods: {
		onPropertyChange(e) {
			this.$emit('update:value', e.target.value)
			this.debouncePropertyChange(e.target.value.trim())
		},

		debouncePropertyChange: debounce(async function(value) {
			if (!this.emptyStringValid) {
				if (value !== '') {
					await this.updateProperty(value)
				}
			} else {
				await this.updateProperty(value)
			}
		}, 500),

		async updateProperty(value) {
			try {
				const responseData = await savePrimaryAccountProperty(
					this.name,
					value,
				)
				this.handleResponse({
					value,
					status: responseData.ocs?.meta?.status,
				})
			} catch (e) {
				this.handleResponse({
					errorMessage: t('settings', 'Unable to update {property}', { property: this.readable.toLocaleLowerCase() }),
					error: e,
				})
			}
		},

		handleResponse({ value, status, errorMessage, error }) {
			if (status === 'ok') {
				this.initialValue = value
				if (this.onSave) {
					this.onSave(value)
				}
				this.showCheckmarkIcon = true
				setTimeout(() => { this.showCheckmarkIcon = false }, 2000)
			} else {
				showError(errorMessage)
				logger.error(errorMessage, error)
				this.showErrorIcon = true
				setTimeout(() => { this.showErrorIcon = false }, 2000)
			}
		},

		onScopeChange(scope) {
			this.$emit('update:scope', scope)
		},
	},
}
</script>

<style lang="scss" scoped>
.property {
	display: grid;
	align-items: center;

	textarea {
		resize: vertical;
		grid-area: 1 / 1;
		width: 100%;
		margin: 3px 3px 3px 0;
		padding: 7px 6px;
		color: var(--color-main-text);
		border: 1px solid var(--color-border-dark);
		border-radius: var(--border-radius);
		background-color: var(--color-main-background);
		font-family: var(--font-face);
		cursor: text;

		&:hover,
		&:focus,
		&:active {
			border-color: var(--color-primary-element) !important;
			outline: none !important;
		}
	}

	input {
		grid-area: 1 / 1;
		width: 100%;
		height: 34px;
		margin: 3px 3px 3px 0;
		padding: 7px 6px;
		color: var(--color-main-text);
		border: 1px solid var(--color-border-dark);
		border-radius: var(--border-radius);
		background-color: var(--color-main-background);
		font-family: var(--font-face);
		cursor: text;
	}

	.property__actions-container {
		grid-area: 1 / 1;
		justify-self: flex-end;
		align-self: flex-end;
		height: 30px;

		display: flex;
		gap: 0 2px;
		margin-right: 5px;
		margin-bottom: 5px;
	}
}

.fade-enter,
.fade-leave-to {
	opacity: 0;
}

.fade-enter-active {
	transition: opacity 200ms ease-out;
}

.fade-leave-active {
	transition: opacity 300ms ease-out;
}
</style>
