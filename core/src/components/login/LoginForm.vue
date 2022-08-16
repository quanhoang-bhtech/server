<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<div />
</template>

<script>
import jstz from 'jstimezonedetect'

import {
	generateUrl,
	imagePath,
} from '@nextcloud/router'

export default {
	name: 'LoginForm',
	props: {
		username: {
			type: String,
			default: '',
		},
		redirectUrl: {
			type: [String, Boolean],
			default: false,
		},
		errors: {
			type: Array,
			default: () => [],
		},
		messages: {
			type: Array,
			default: () => [],
		},
		throttleDelay: {
			type: Number,
			default: 0,
		},
		invertedColors: {
			type: Boolean,
			default: false,
		},
		autoCompleteAllowed: {
			type: Boolean,
			default: true,
		},
		directLogin: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			loading: false,
			timezone: jstz.determine().name(),
			timezoneOffset: (-new Date().getTimezoneOffset() / 60),
			user: this.username,
			password: '',
			passwordInputType: 'password',
		}
	},
	computed: {
		apacheAuthFailed() {
			return this.errors.indexOf('apacheAuthFailed') !== -1
		},
		internalException() {
			return this.errors.indexOf('internalexception') !== -1
		},
		invalidPassword() {
			return this.errors.indexOf('invalidpassword') !== -1
		},
		userDisabled() {
			return this.errors.indexOf('userdisabled') !== -1
		},
		toggleIcon() {
			return imagePath('core', 'actions/toggle.svg')
		},
		loadingIcon() {
			return imagePath('core', 'loading-dark.gif')
		},
		loginActionUrl() {
			return generateUrl('login')
		},
	},
	mounted() {
		if (this.username === '') {
			this.$refs.user.focus()
		} else {
			this.$refs.password.focus()
		}
	},
	methods: {
		togglePassword() {
			if (this.passwordInputType === 'password') {
				this.passwordInputType = 'text'
			} else {
				this.passwordInputType = 'password'
			}
		},
		updateUsername() {
			this.$emit('update:username', this.user)
		},
		submit() {
			this.loading = true
			this.$emit('submit')
		},
	},
}
</script>

<style scoped>

</style>
