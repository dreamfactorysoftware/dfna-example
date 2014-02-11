<?php
/**
 * This file is part of the DreamFactory DFNA Application
 * Copyright 2013 DreamFactory Software, Inc. {@email support@dreamfactory.com}
 *
 * DreamFactory DFNA Application {@link http://github.com/dreamfactorysoftware/dfna-example}
 * DreamFactory Oasys(tm) {@link http://github.com/dreamfactorysoftware/oasys}
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
use DreamFactory\Platform\Enums\LocalStorageTypes;
use DreamFactory\Platform\Utility\Platform;
use DreamFactory\Yii\Utility\Pii;

/**
 * PortalSandboxTest
 */
class PortalSandboxTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers DreamFactory\Platform\Utility\Platform::getStoragePath()
	 */
	public function testGetStoragePath()
	{
		$_control = Pii::getParam( LocalStorageTypes::STORAGE_PATH );
		$this->assertEquals( $_control, Platform::getStoragePath() );
	}

	/**
	 * @covers DreamFactory\Platform\Utility\Platform::getPrivatePath()
	 */
	public function testGetPrivatePath()
	{
		$_control = Pii::getParam( LocalStorageTypes::PRIVATE_PATH );
		$this->assertEquals( $_control, Platform::getPrivatePath() );
	}

	/**
	 * @covers DreamFactory\Platform\Utility\Platform::getSnapshotPath()
	 */
	public function testGetSnapshotPath()
	{
		$_control = Pii::getParam( LocalStorageTypes::SNAPSHOT_PATH );
		$this->assertEquals( $_control, Platform::getSnapshotPath() );
	}

	/**
	 * @covers DreamFactory\Platform\Utility\Platform::getStoragePath()
	 */
	public function testGetLibraryPath()
	{
		$_control = Pii::getParam( LocalStorageTypes::LIBRARY_PATH );
		$this->assertEquals( $_control, Platform::getLibraryPath() );
	}
}
