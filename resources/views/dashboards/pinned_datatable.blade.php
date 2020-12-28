@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

{{-- <div class="preloader"></div> --}}

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		{{-- @include('shared.navbar') --}}

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50">
			    <div class="row">
			    	<div class="col-sm-12">

                              <div class="col-sm-3 pl0">
                                    <div class="form-group">
                                          <label>Select # of Columns to Freeze</label>
                                          <select name="numb_pinned_cols" id="numb_pinned_cols" class="form-control numb_pinned_cols">
                                                <option value="">Select One</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                          </select>
                                    </div>
                              </div>

                              <div class="table-responsive">
                                    <table class="table table-striped table-hover stripe row-border order-column" id="pin_col_dataTable" style="width:100%">
                                          <thead>
                                                <tr>
                                                      <th><span>Date</span></th>
                                                      <th><span>Rep</span></th>
                                                      <th><span>Campaign</span></th>
                                                      <th><span>Subcampaign</span></th>
                                                      <th><span>Phone</span></th>
                                                      <th><span>Attempt</span></th>
                                                      <th><span>Caller ID</span></th>
                                                      <th><span>Inbound Source</span></th>
                                                      <th><span>Last</span></th>
                                                      <th><span>First</span></th>
                                                      <th><span>Import Date</span></th>
                                                      <th><span>Call Status</span></th>
                                                      <th><span>Is Callable</span></th>
                                                      <th><span>Duration</span></th>
                                                      <th><span>Call Type</span></th>
                                                      <th><span>Details</span></th>
                                                      <th><span>Hangup</span></th>
                                                      <th><span>Route</span></th>
                                                      <th><span>Recording</span></th>
                                                </tr>
                                          </thead>

                                          <tbody>
                                                <tr>
                                                      <td>12/14/2020 9:10 AM</td>
                                                      <td>Ken</td>
                                                      <td>New Support</td>
                                                      <td></td>
                                                      <td>14022010541</td>
                                                      <td>1</td>
                                                      <td>8887398218</td>
                                                      <td>Main Number</td>
                                                      <td></td>
                                                      <td></td>
                                                      <td>12/14/2020 9:09 AM</td>
                                                      <td>Wrong Number</td>
                                                      <td>0</td>
                                                      <td>00:02:00</td>
                                                      <td>Inbound</td>
                                                      <td></td>
                                                      <td></td>
                                                      <td>dialer07.pstn.twilio.com:5060</td>
                                                      <td></td>
                                                </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:16 AM</td>
                                                                                  <td>1 - 9542464086</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>12154027200</td>
                                                                                  <td>1</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 9:15 AM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:21 AM</td>
                                                                                  <td>Dan Cleary</td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>12154027200</td>
                                                                                  <td>1</td>
                                                                                  <td>9542464086</td>
                                                                                  <td>SALES_CALL</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 9:16 AM</td>
                                                                                  <td>Inbound Voicemail</td>
                                                                                  <td>0</td>
                                                                                  <td>00:05:30</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::ProcessIVR&lt;=&gt;m::tmInboundSource&lt;=&gt;d::9542464086&lt;=&gt;f::TerminationRoutine&lt;=&gt;m::pmTerminationVoicemail&lt;=&gt;d::2008</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:38 AM</td>
                                                                                  <td>Nick D</td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>27</td>
                                                                                  <td>9548664647</td>
                                                                                  <td>Nicks Personal DID</td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:36</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td>Agent Hangup</td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:38 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>2015</td>
                                                                                  <td>28</td>
                                                                                  <td>7543030010</td>
                                                                                  <td></td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>CR_DISCONNECTED</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:38 AM</td>
                                                                                  <td></td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>28</td>
                                                                                  <td>9548664647</td>
                                                                                  <td>Nicks Personal DID</td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>Inbound Transfer</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::TerminationRoutine&lt;=&gt;m::pmTerminationExtension&lt;=&gt;d::2015&lt;=&gt;CR_DISCONNECTED</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:39 AM</td>
                                                                                  <td>Nick D</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>2</td>
                                                                                  <td>8887398218</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>09/23/2020 4:40 PM</td>
                                                                                  <td>Existing Contact</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>sip.telnyx.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:42 AM</td>
                                                                                  <td>Nick D</td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>29</td>
                                                                                  <td>8887398218</td>
                                                                                  <td></td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>Existing Contact</td>
                                                                                  <td>0</td>
                                                                                  <td>00:02:18</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td>Agent Hangup</td>
                                                                                  <td>sip.telnyx.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:43 AM</td>
                                                                                  <td></td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td>14848097672</td>
                                                                                  <td>1</td>
                                                                                  <td>4432965523</td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 9:42 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:24</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 9:57 AM</td>
                                                                                  <td>2 - 8559342537</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>15069881688</td>
                                                                                  <td>60</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td>The Call Guys</td>
                                                                                  <td>Serge / Darcey</td>
                                                                                  <td>04/17/2018 10:57 AM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:00 AM</td>
                                                                                  <td>Dylan F</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>15069881688</td>
                                                                                  <td>61</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>The Call Guys</td>
                                                                                  <td>Serge / Darcey</td>
                                                                                  <td>04/17/2018 10:57 AM</td>
                                                                                  <td>TRANSFERRED</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Transferred</td>
                                                                                  <td>Transferred Call</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:00 AM</td>
                                                                                  <td>danielle</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>15069881688</td>
                                                                                  <td>61</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>The Call Guys</td>
                                                                                  <td>Serge / Darcey</td>
                                                                                  <td>04/17/2018 10:57 AM</td>
                                                                                  <td>TRANSFERRED</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Unknown</td>
                                                                                  <td>Transferred Call</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:00 AM</td>
                                                                                  <td></td>
                                                                                  <td>Dylans test campaign</td>
                                                                                  <td></td>
                                                                                  <td>17542190088</td>
                                                                                  <td>1</td>
                                                                                  <td>7542538313</td>
                                                                                  <td>Twilio Set</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:00 AM</td>
                                                                                  <td>Inbound Voicemail</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:18</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::TerminationRoutine&lt;=&gt;m::pmTerminationVoicemail</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:01 AM</td>
                                                                                  <td></td>
                                                                                  <td>Aspect Users</td>
                                                                                  <td></td>
                                                                                  <td>12174628989</td>
                                                                                  <td>1</td>
                                                                                  <td>7472467972</td>
                                                                                  <td>Aspect Users</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:01 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:42</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:03 AM</td>
                                                                                  <td>2 - 8559342537</td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>18778546678</td>
                                                                                  <td>8</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td>Darby</td>
                                                                                  <td>Jason</td>
                                                                                  <td>11/18/2020 10:03 AM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:11 AM</td>
                                                                                  <td>Dylan F</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>15069881688</td>
                                                                                  <td>61</td>
                                                                                  <td>8559342537</td>
                                                                                  <td></td>
                                                                                  <td>The Call Guys</td>
                                                                                  <td>Serge / Darcey</td>
                                                                                  <td>04/17/2018 10:57 AM</td>
                                                                                  <td>Task Completed</td>
                                                                                  <td>0</td>
                                                                                  <td>00:15:06</td>
                                                                                  <td>Transferred</td>
                                                                                  <td>f::ProcessIVR&lt;=&gt;m::tmInboundSource&lt;=&gt;d::8559342537</td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:20 AM</td>
                                                                                  <td>Ken</td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>18778546678</td>
                                                                                  <td>9</td>
                                                                                  <td>8559342537</td>
                                                                                  <td>demo support</td>
                                                                                  <td>Darby</td>
                                                                                  <td>Jason</td>
                                                                                  <td>11/18/2020 10:03 AM</td>
                                                                                  <td>Existing Contact</td>
                                                                                  <td>0</td>
                                                                                  <td>00:17:42</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::ProcessIVR&lt;=&gt;m::tmInboundSource&lt;=&gt;d::8559342537</td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:22 AM</td>
                                                                                  <td></td>
                                                                                  <td>Aspect Users</td>
                                                                                  <td></td>
                                                                                  <td>18666772706</td>
                                                                                  <td>15</td>
                                                                                  <td>6144824182</td>
                                                                                  <td>Aspect Users</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>03/19/2020 1:04 PM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:18</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:23 AM</td>
                                                                                  <td></td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td>14408708318</td>
                                                                                  <td>1</td>
                                                                                  <td>4407403229</td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:22 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:42</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:33 AM</td>
                                                                                  <td>Dylan F</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>14026574220</td>
                                                                                  <td>44</td>
                                                                                  <td>9547373086</td>
                                                                                  <td>Dylan Personal DID</td>
                                                                                  <td></td>
                                                                                  <td>Claire</td>
                                                                                  <td>08/27/2020 9:10 AM</td>
                                                                                  <td>Inbound Voicemail</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:36</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::TerminationRoutine&lt;=&gt;m::pmTerminationVoicemail&lt;=&gt;e::2031&lt;=&gt;d::2031</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:34 AM</td>
                                                                                  <td>2 - 8559342537</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>14026574220</td>
                                                                                  <td>45</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td></td>
                                                                                  <td>Claire</td>
                                                                                  <td>08/27/2020 9:10 AM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:37 AM</td>
                                                                                  <td>danielle</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>14026574220</td>
                                                                                  <td>46</td>
                                                                                  <td>8559342537</td>
                                                                                  <td>demo support</td>
                                                                                  <td></td>
                                                                                  <td>Claire</td>
                                                                                  <td>08/27/2020 9:10 AM</td>
                                                                                  <td>Task Completed</td>
                                                                                  <td>0</td>
                                                                                  <td>00:03:42</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::ProcessIVR&lt;=&gt;m::tmInboundSource&lt;=&gt;d::8559342537</td>
                                                                                  <td>Agent Hangup</td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:39 AM</td>
                                                                                  <td>joec</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>16194085116</td>
                                                                                  <td>5</td>
                                                                                  <td>9543985669</td>
                                                                                  <td></td>
                                                                                  <td>Scott</td>
                                                                                  <td>Brett</td>
                                                                                  <td>08/17/2020 3:46 PM</td>
                                                                                  <td>Left Message</td>
                                                                                  <td>1</td>
                                                                                  <td>00:01:42</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>sip.telnyx.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:40 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>2015</td>
                                                                                  <td>110</td>
                                                                                  <td>6617480240</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>Allen</td>
                                                                                  <td>03/02/2015 2:32 PM</td>
                                                                                  <td>CR_DISCONNECTED</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:40 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>16617480240</td>
                                                                                  <td>110</td>
                                                                                  <td>9548664647</td>
                                                                                  <td>Nicks Personal DID</td>
                                                                                  <td></td>
                                                                                  <td>Allen</td>
                                                                                  <td>03/02/2015 2:32 PM</td>
                                                                                  <td>Inbound Transfer</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::TerminationRoutine&lt;=&gt;m::pmTerminationExtension&lt;=&gt;d::2015&lt;=&gt;CR_DISCONNECTED</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:47 AM</td>
                                                                                  <td>2 - 8559342537</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>14026574220</td>
                                                                                  <td>47</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td></td>
                                                                                  <td>Claire</td>
                                                                                  <td>08/27/2020 9:10 AM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:48 AM</td>
                                                                                  <td>Dan Cleary</td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>16144662319</td>
                                                                                  <td>2</td>
                                                                                  <td>9548403524</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>01/21/2020 1:17 PM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:05:18</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>sip.telnyx.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:53 AM</td>
                                                                                  <td>2 - 8559342537</td>
                                                                                  <td>New Support</td>
                                                                                  <td>juen 14 call back</td>
                                                                                  <td>19546096336</td>
                                                                                  <td>1282</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td>Mouradian</td>
                                                                                  <td>Tom </td>
                                                                                  <td>02/19/2015 6:21 PM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:53 AM</td>
                                                                                  <td>2 - 8559342537</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>12034569085</td>
                                                                                  <td>1</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:53 AM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>51.81.31.121</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:55 AM</td>
                                                                                  <td>Ken</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>12034569085</td>
                                                                                  <td>2</td>
                                                                                  <td>8559342537</td>
                                                                                  <td>demo support</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:53 AM</td>
                                                                                  <td>Ticket Created</td>
                                                                                  <td>0</td>
                                                                                  <td>00:01:48</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::ProcessIVR&lt;=&gt;m::tmInboundSource&lt;=&gt;d::8559342537</td>
                                                                                  <td></td>
                                                                                  <td>51.81.31.121</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:55 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>13525970116</td>
                                                                                  <td>1</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:55 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:57 AM</td>
                                                                                  <td></td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td>12523073666</td>
                                                                                  <td>1</td>
                                                                                  <td>2525657410</td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:56 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:48</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 10:58 AM</td>
                                                                                  <td>Dylan F</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>14026574220</td>
                                                                                  <td>48</td>
                                                                                  <td>8559342537</td>
                                                                                  <td>demo support</td>
                                                                                  <td></td>
                                                                                  <td>Claire</td>
                                                                                  <td>08/27/2020 9:10 AM</td>
                                                                                  <td>Task Completed</td>
                                                                                  <td>0</td>
                                                                                  <td>00:11:06</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::ProcessIVR&lt;=&gt;m::tmInboundSource&lt;=&gt;d::8559342537</td>
                                                                                  <td>Agent Hangup</td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:00 AM</td>
                                                                                  <td></td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td>14055610582</td>
                                                                                  <td>1</td>
                                                                                  <td>4056213813</td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 10:59 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:48</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:00 AM</td>
                                                                                  <td>Nick D</td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>30</td>
                                                                                  <td>8887398218</td>
                                                                                  <td></td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>Left Message</td>
                                                                                  <td>1</td>
                                                                                  <td>00:01:00</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>sip.telnyx.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:03 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>19063962582</td>
                                                                                  <td>1</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 11:03 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:05 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>2015</td>
                                                                                  <td>31</td>
                                                                                  <td>7543030010</td>
                                                                                  <td></td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>CR_DISCONNECTED</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:05 AM</td>
                                                                                  <td></td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>31</td>
                                                                                  <td>9548664647</td>
                                                                                  <td>Nicks Personal DID</td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>Inbound Transfer</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::TerminationRoutine&lt;=&gt;m::pmTerminationExtension&lt;=&gt;d::2015&lt;=&gt;CR_DISCONNECTED</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:05 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>2015</td>
                                                                                  <td>32</td>
                                                                                  <td>7543030010</td>
                                                                                  <td></td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>CR_DISCONNECTED</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:05 AM</td>
                                                                                  <td></td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>32</td>
                                                                                  <td>9548664647</td>
                                                                                  <td>Nicks Personal DID</td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>Inbound Transfer</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::TerminationRoutine&lt;=&gt;m::pmTerminationExtension&lt;=&gt;d::2015&lt;=&gt;CR_DISCONNECTED</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:06 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>16194085116</td>
                                                                                  <td>6</td>
                                                                                  <td>9543985669</td>
                                                                                  <td>Joec Caller ID</td>
                                                                                  <td>Scott</td>
                                                                                  <td>Brett</td>
                                                                                  <td>08/17/2020 3:46 PM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:24</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:08 AM</td>
                                                                                  <td></td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>2015</td>
                                                                                  <td>33</td>
                                                                                  <td>7543030010</td>
                                                                                  <td></td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>CR_DISCONNECTED</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:08 AM</td>
                                                                                  <td></td>
                                                                                  <td>Request Demo-2</td>
                                                                                  <td></td>
                                                                                  <td>17543030010</td>
                                                                                  <td>33</td>
                                                                                  <td>9548664647</td>
                                                                                  <td>Nicks Personal DID</td>
                                                                                  <td>Schlosser</td>
                                                                                  <td>Helen</td>
                                                                                  <td>09/23/2020 4:41 PM</td>
                                                                                  <td>Inbound Transfer</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:12</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::TerminationRoutine&lt;=&gt;m::pmTerminationExtension&lt;=&gt;d::2015&lt;=&gt;CR_DISCONNECTED</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:10 AM</td>
                                                                                  <td></td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td>18654440450</td>
                                                                                  <td>1</td>
                                                                                  <td>8652696154</td>
                                                                                  <td>CallerID Callbacks</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>12/14/2020 11:09 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:00:48</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:16 AM</td>
                                                                                  <td>joec</td>
                                                                                  <td>New Support</td>
                                                                                  <td>juen 14 call back</td>
                                                                                  <td>19546096336</td>
                                                                                  <td>1283</td>
                                                                                  <td>8559342537</td>
                                                                                  <td>demo support</td>
                                                                                  <td>Mouradian</td>
                                                                                  <td>Tom </td>
                                                                                  <td>02/19/2015 6:21 PM</td>
                                                                                  <td>Ticket Created</td>
                                                                                  <td>0</td>
                                                                                  <td>00:23:06</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::ProcessIVR&lt;=&gt;m::tmInboundSource&lt;=&gt;d::8559342537</td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:16 AM</td>
                                                                                  <td>Dan Cleary</td>
                                                                                  <td>New Prospects</td>
                                                                                  <td></td>
                                                                                  <td>8444480325</td>
                                                                                  <td>3</td>
                                                                                  <td>9548403524</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>11/04/2020 10:17 AM</td>
                                                                                  <td>CR_HANGUP</td>
                                                                                  <td>1</td>
                                                                                  <td>00:06:06</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>sip.telnyx.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:19 AM</td>
                                                                                  <td>Ken</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>17183364200</td>
                                                                                  <td>373</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td>Petroff Amshen</td>
                                                                                  <td>Main Number</td>
                                                                                  <td>04/26/2018 4:02 PM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:07:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td>f::ProcessDigits&lt;=&gt;m::29</td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:19 AM</td>
                                                                                  <td>2 - 8559342537</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>19493078870</td>
                                                                                  <td>85</td>
                                                                                  <td>8887398218</td>
                                                                                  <td>Main Number</td>
                                                                                  <td>Kim</td>
                                                                                  <td>Kris</td>
                                                                                  <td>06/13/2018 2:19 PM</td>
                                                                                  <td></td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Inbound</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>dialer07.pstn.twilio.com:5060</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:26 AM</td>
                                                                                  <td>Dan Cleary</td>
                                                                                  <td>ans machine test</td>
                                                                                  <td></td>
                                                                                  <td>19546006242</td>
                                                                                  <td>53</td>
                                                                                  <td>9548403524</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>01/07/2020 12:31 PM</td>
                                                                                  <td>Existing Contact</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:36</td>
                                                                                  <td>Manual</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>sip.telnyx.com</td>
                                                                                  <td></td>
                                                          </tr>
                                      <tr>

                                                                  <td>12/14/2020 11:28 AM</td>
                                                                                  <td>Ken</td>
                                                                                  <td>New Support</td>
                                                                                  <td></td>
                                                                                  <td>17183364200</td>
                                                                                  <td>374</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td>Petroff Amshen</td>
                                                                                  <td>Main Number</td>
                                                                                  <td>04/26/2018 4:02 PM</td>
                                                                                  <td>TRANSFERRED</td>
                                                                                  <td>0</td>
                                                                                  <td>00:00:00</td>
                                                                                  <td>Transferred</td>
                                                                                  <td>Transferred Call</td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                                                  <td></td>
                                                          </tr>
                                                    </tbody>
                                        </table>
                              </div>
	            	</div>
				</div>
			</div>
		</div>
	</div>

@include('shared.notifications_bar')

@endsection

