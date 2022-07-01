<?php

declare(strict_types=1);

/**
 * @copyright 2017, Georg Ehrke <oc.list@georgehrke.com>
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Georg Ehrke <oc.list@georgehrke.com>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\DAV\CalDAV;

use OCA\DAV\CalDAV\Auth\CustomPrincipalPlugin;
use OCA\DAV\CalDAV\InvitationResponse\InvitationResponseServer;
use OCP\Calendar\Exceptions\CalendarException;
use OCP\Calendar\ICreateFromString;
use OCP\Constants;
use OCP\Security\ISecureRandom;
use Sabre\DAV\Exception\Conflict;
use Sabre\VObject\Reader;
use function Sabre\Uri\split as uriSplit;

class CalendarImpl implements ICreateFromString {

	/** @var CalDavBackend */
	private $backend;

	/** @var Calendar */
	private $calendar;

	/** @var array */
	private $calendarInfo;
	private $random;

	/**
	 * CalendarImpl constructor.
	 *
	 * @param Calendar $calendar
	 * @param array $calendarInfo
	 * @param CalDavBackend $backend
	 */
	public function __construct(Calendar $calendar,
								array $calendarInfo,
								CalDavBackend $backend,
								ISecureRandom $random) {
		$this->calendar = $calendar;
		$this->calendarInfo = $calendarInfo;
		$this->backend = $backend;
		$this->random = $random; // possibly create filename in mail already?
	}

	/**
	 * @return string defining the technical unique key
	 * @since 13.0.0
	 */
	public function getKey() {
		return $this->calendarInfo['id'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUri(): string {
		return $this->calendarInfo['uri'];
	}

	/**
	 * In comparison to getKey() this function returns a human readable (maybe translated) name
	 * @return null|string
	 * @since 13.0.0
	 */
	public function getDisplayName() {
		return $this->calendarInfo['{DAV:}displayname'];
	}

	/**
	 * Calendar color
	 * @return null|string
	 * @since 13.0.0
	 */
	public function getDisplayColor() {
		return $this->calendarInfo['{http://apple.com/ns/ical/}calendar-color'];
	}

	/**
	 * @param string $pattern which should match within the $searchProperties
	 * @param array $searchProperties defines the properties within the query pattern should match
	 * @param array $options - optional parameters:
	 * 	['timerange' => ['start' => new DateTime(...), 'end' => new DateTime(...)]]
	 * @param integer|null $limit - limit number of search results
	 * @param integer|null $offset - offset for paging of search results
	 * @return array an array of events/journals/todos which are arrays of key-value-pairs
	 * @since 13.0.0
	 */
	public function search($pattern, array $searchProperties = [], array $options = [], $limit = null, $offset = null) {
		return $this->backend->search($this->calendarInfo, $pattern,
			$searchProperties, $options, $limit, $offset);
	}

	/**
	 * @return integer build up using \OCP\Constants
	 * @since 13.0.0
	 */
	public function getPermissions() {
		$permissions = $this->calendar->getACL();
		$result = 0;
		foreach ($permissions as $permission) {
			switch ($permission['privilege']) {
				case '{DAV:}read':
					$result |= Constants::PERMISSION_READ;
					break;
				case '{DAV:}write':
					$result |= Constants::PERMISSION_CREATE;
					$result |= Constants::PERMISSION_UPDATE;
					break;
				case '{DAV:}all':
					$result |= Constants::PERMISSION_ALL;
					break;
			}
		}

		return $result;
	}

	/**
	 * Create a new calendar event for this calendar
	 * by way of an ICS string
	 *
	 * @param string $name the file name - needs to contan the .ics ending
	 * @param string $calendarData a string containing a valid VEVENT ics
	 *
	 * @throws CalendarException
	 */
	public function createFromString(string $name, string $calendarData): void {
		$server = new InvitationResponseServer(false);

		/** @var CustomPrincipalPlugin $plugin */
		$plugin = $server->server->getPlugin('auth');
		// we're working around the previous implementation
		// that only allowed the public system principal to be used
		// so set the custom principal here
		$plugin->setCurrentPrincipal($this->calendar->getPrincipalURI());

		if (empty($this->calendarInfo['uri'])) {
			throw new CalendarException('Could not write to calendar as URI parameter is missing');
		}

		// Build full calendar path
		[, $user] = uriSplit($this->calendar->getPrincipalURI());
		$fullCalendarFilename = sprintf('calendars/%s/%s/%s', $user, $this->calendarInfo['uri'], $name);

		// Force calendar change URI
		/** @var Schedule\Plugin $schedulingPlugin */
		$schedulingPlugin = $server->server->getPlugin('caldav-schedule');
		$schedulingPlugin->setPathOfCalendarObjectChange($fullCalendarFilename);

		$stream = fopen('php://memory', 'rb+');
		fwrite($stream, $calendarData);
		rewind($stream);
		try {
			$server->server->createFile($fullCalendarFilename, $stream);
		} catch (Conflict $e) {
			throw new CalendarException('Could not create new calendar event: ' . $e->getMessage(), 0, $e);
		} finally {
			fclose($stream);
		}
	}

	// prcess invitation response here
	// check security implications here
	// pass on to createFromString or similar (talk to richard)

	// @todo think about $recipient. is it neccessary?
//	public function handleInvitationReply(string $sender, string $recipient, string $calendarData): void {
//		$vObject =  Reader::read($calendarData);
//		$vEvent = $vObject->{'VEVENT'};
//
//		// First, we check if the correct method is passed to us
//		// REPLY: the attendee has to be updated in the ORGANIZER calendar
//		if(strcasecmp('REPLY', $vEvent->{'METHOD'}->getValue()) !== 0) {
//			return;
//		}
//
//		// check if mail recipient and organizer are one and the same
//		$organizer = substr($vEvent->{'ORGANIZER'}->getValue(), 7);
//
//		if(strcasecmp($recipient, $organizer) !== 0) {
//			return;
//		}
//
//		//check if the event is in the future
//
//		// get the original and use it for further comparisons here:
//		$caldavBackend = $this->calendar->caldavBackend;
//		$original = $caldavBackend->search($this->calendarInfo, 'UID', [$vEvent->{'UID'}], [], 1, 0);
//
//		if(empty($original)) {
//			throw new CalendarException('Could not find event in calendar ' . $this->getDisplayName());
//		}
//
//		$originalVevent = Reader::read($original[0])->{'VEVENT'};
//
//		// check if the organizer in the attached calendar data is the one in the original event
//		if(strcasecmp($originalVevent->{'ORGANIZER'}->getValue(), $vEvent->{'ORGANIZER'}->getValue()) !== 0) {
//			return;
//		}
//
//		// we need to compare the email address the REPLY is coming from (in Mail)
//		// to the email address in the ATTENDEE as specified in the RFC
//		$sender = $this->request->getParam('sender');
//		$attendee = substr($vEvent->{'ATTENDEE'}->getValue(), 7);
//
//		// Sabre is doing this but is letting newly added attendees "party crash"
//		// but we should not allow modification here
//		// @todo we need to check the current attendee agains the existing attendees in
//		// the original VEVENT
//
//		// get the original event here - search by UID and principal
//
//		if(strcasecmp($sender, $attendee) !== 0) {
//			return;
//		}
//
//
//		// check attendee against the attendee list here
//		$attendees = $originalVevent->{'ATTENDEE'}; // this is wrong, I need to search further
//
//		foreach( $attendees as $a) {
//			if(strcasecmp($a->getValue(), $vEvent->{'ATTENDEE'}->getValue()) !== 0) {
//				return;
//			}
//		}
//
//		$filename = $this->random->generate(32, ISecureRandom::CHAR_ALPHANUMERIC);
//
//		$this->createFromString($filename, $calendarData);
//
//	}
//
//	public function handleInvitationCancel(): void {
//		$vObject =  Reader::read($this->request->getParam('scheduling'));
//		$vEvent = $vObject->{'VEVENT'};
//
//		// First, we check if the correct method is passed to us
//		// CANCEL: the STATUS has to be updated in the ATTENDEEs calendar
//		if(strcasecmp('CANCEL', $vEvent->{'METHOD'}->getValue()) !== 0) {
//			return new JSONResponse(['Could not handle request'], Http::STATUS_NOT_IMPLEMENTED);
//		}
//
//		// Secondly, check if mail recipient and attendee are one and the same
//		$recipient = $this->request->getParam('recipient');
//		$attendee = substr($vEvent->{'ATTENDEE'}->getValue(), 7);
//
//		if(strcasecmp($recipient, $attendee) !== 0) {
//			return new JSONResponse(['Unable to modify event'], Http::STATUS_FORBIDDEN);
//		}
//
//		// Thirdly, we need to compare the email address the CANCEL is coming from (in Mail)
//		// to the email address in the ORGANIZER.
//		// We don't want to accept a CANCEL request from just anyone
//		$sender = $this->request->getParam('sender');
//		$organizer = substr($vEvent->{'ORGANIZER'}->getValue(), 7);
//
//		if(strcasecmp($sender, $organizer) !== 0) {
//			return new JSONResponse(['Unable to modify event'], Http::STATUS_FORBIDDEN);
//		}
//
//
//
//		// Build the iTIP Message and let Sabre handle the rest
//		$iTipMessage = new Message();
//		$iTipMessage->uid = $vEvent->{'UID'}->getValue();
//		$iTipMessage->component = 'VEVENT';
//		$iTipMessage->method = $vEvent->{'METHOD'}->getValue();
//		$iTipMessage->sequence =  $vEvent->{'SEQUENCE'}->getValue();
//		$iTipMessage->sender = $attendee;
//		$iTipMessage->recipient =  $organizer;
//		$iTipMessage->message = $vObject;
//
//		$this->responseServer->handleITipMessage($iTipMessage);
//
//		if ($iTipMessage->getScheduleStatus() === '1.2') {
//			return new JSONResponse([$iTipMessage->scheduleStatus]);
//		}
//		return new JSONResponse([$iTipMessage->scheduleStatus], Http::STATUS_UNPROCESSABLE_ENTITY);
//
//	}
}
