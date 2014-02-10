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
/**
 * Main entry point/bootstrap for PHP applications
 */
if ( !class_exists( '\\Yii', false ) )
{
	$_dspBase = realpath( __DIR__ );

	while ( true )
	{
		if ( file_exists( $_dspBase . '/docs/rocket.psd' ) || is_dir( $_dspBase . '/storage/.private' ) )
		{
			break;
		}

		$_dspBase = dirname( $_dspBase );

		if ( empty( $_dspBase ) || $_dspBase == '.' || $_dspBase == '/' )
		{
			throw new Exception( 'Unable to locate DSP installation.', 500 );
		}
	}

	//	Load up composer...
	$_autoloader = require_once( $_dspBase . '/vendor/autoload.php' );

	if ( is_object( $_autoloader ) )
	{
		\Kisma::set( 'app.autoloader', $_autoloader );
	}
	else
	{
		$_autoloader = \Kisma::get( 'app.autoloader' );
	}

	//	Turn on debugging
	\Kisma::setDebug( true );

	//	Load up Yii
	require_once $_dspBase . '/vendor/dreamfactory/yii/framework/yii.php';

	if ( \Kisma::getDebug() )
	{
		//	Yii debug settings
		defined( 'YII_DEBUG' ) or define( 'YII_DEBUG', true );
		defined( 'YII_TRACE_LEVEL' ) or define( 'YII_TRACE_LEVEL', 3 );
	}

	if ( !\Yii::app() )
	{
		//	Create the application but do not run...
		DreamFactory\Yii\Utility\Pii::run(
			__DIR__ . '/src',
			is_object( $_autoloader ) ? $_autoloader : null,
			'DreamFactory\\Platform\\Yii\\Components\\PlatformWebApplication',
			$_dspBase . '/config/web.php',
			false,
			false
		);
	}
}
