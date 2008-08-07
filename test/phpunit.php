<?php
//
// PHP framework for testing, based on the design of "JUnit".
//
// Written by Fred Yankowski <fred@ontosys.com>
//            OntoSys, Inc  <http://www.OntoSys.com>
//
// $Id$

// Copyright (c) 2000 Fred Yankowski

// Permission is hereby granted, free of charge, to any person
// obtaining a copy of this software and associated documentation
// files (the "Software"), to deal in the Software without
// restriction, including without limitation the rights to use, copy,
// modify, merge, publish, distribute, sublicense, and/or sell copies
// of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
// BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
// ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
// CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
//
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE |
		E_CORE_ERROR | E_CORE_WARNING);

/*
interface Test {
  function run(&$aTestResult);
  function countTestCases();
}
*/

function trace($msg) {
  return;
  print($msg);
  flush();
}


class Exception {
    /* Emulate a Java exception, sort of... */
  var $message;
  function Exception($message) {
    $this->message = $message;
  }
  function getMessage() {
    return $this->message;
  }
}

class Assert {
  function assert($boolean, $message=0) {
    if (! $boolean)
      $this->fail($message);
  }

  function assertEquals($expected, $actual, $message=0) {
    if ($expected != $actual) {
      $this->failNotEquals($expected, $actual, "expected", $message);
    }
  }

  function assertRegexp($regexp, $actual, $message=false) {
    if (! preg_match($regexp, $actual)) {
      $this->failNotEquals($regexp, $actual, "pattern", $message);
    }
  }

  function failNotEquals($expected, $actual, $expected_label, $message=0) {
    // Private function for reporting failure to match.
    $str = $message ? ($message . ' ') : '';
    $str .= "($expected_label/actual)<br>";
    $htmlExpected = htmlspecialchars($expected);
    $htmlActual = htmlspecialchars($actual);
    $str .= sprintf("<pre>%s\n--------\n%s</pre>",
		    $htmlExpected, $htmlActual);
    $this->fail($str);
  }
}

class TestCase extends Assert /* implements Test */ {
  /* Defines context for running tests.  Specific context -- such as
     instance variables, global variables, global state -- is defined
     by creating a subclass that specializes the setUp() and
     tearDown() methods.  A specific test is defined by a subclass
     that specializes the runTest() method. */
  var $fName;
  var $fResult;
  var $fExceptions = array();

  function TestCase($name) {
    $this->fName = $name;
  }

  function run($testResult=0) {
    /* Run this single test, by calling the run() method of the
       TestResult object which will in turn call the runBare() method
       of this object.  That complication allows the TestResult object
       to do various kinds of progress reporting as it invokes each
       test.  Create/obtain a TestResult object if none was passed in.
       Note that if a TestResult object was passed in, it must be by
       reference. */
    if (! $testResult)
      $testResult = $this->_createResult();
    $this->fResult = $testResult;
    $testResult->run(&$this);
    $this->fResult = 0;
    return $testResult;
  }

  function countTestCases() {
    return 1;
  }

  function runTest() {
    $name = $this->name();
    // Since isset($this->$name) is false, no way to run defensive checks
    $this->$name();
  }

  function setUp() /* expect override */ {
    //print("TestCase::setUp()<br>\n");
  }

  function tearDown() /* possible override */ {
    //print("TestCase::tearDown()<br>\n");
  }

  ////////////////////////////////////////////////////////////////


  function _createResult() /* protected */ {
    /* override this to use specialized subclass of TestResult */
    return new TestResult;
  }

  function fail($message=0) {
    //printf("TestCase::fail(%s)<br>\n", ($message) ? $message : '');
    /* JUnit throws AssertionFailedError here.  We just record the
       failure and carry on */
    $this->fExceptions[] = new Exception(&$message);
  }

  function error($message) {
    /* report error that requires correction in the test script
       itself, or (heaven forbid) in this testing infrastructure */
    printf('<b>ERROR: ' . $message . '</b><br>');
    $this->fResult->stop();
  }

  function failed() {
    return count($this->fExceptions);
  }

  function getExceptions() {
    return $this->fExceptions;
  }

  function name() {
    return $this->fName;
  }

  function runBare() {
    $this->setup();
    $this->runTest();
    $this->tearDown();
  }
}


class TestSuite /* implements Test */ {
  /* Compose a set of Tests (instances of TestCase or TestSuite), and
     run them all. */
  var $fTests = array();

  function TestSuite($classname=false) {
    if ($classname) {
      // Find all methods of the given class whose name starts with
      // "test" and add them to the test suite.  We are just _barely_
      // able to do this with PHP's limited introspection...  Note
      // that PHP seems to store method names in lower case, and we
      // have to avoid the constructor function for the TestCase class
      // superclass.  This will fail when $classname starts with
      // "Test" since that will have a constructor method that will
      // get matched below and then treated (incorrectly) as a test
      // method.  So don't name any TestCase subclasses as "Test..."!
      if (floor(phpversion()) >= 4) {
	// PHP4 introspection, submitted by Dylan Kuhn
	$names = get_class_methods($classname);
	while (list($key, $method) = each($names)) {
	  if (preg_match('/^test/', $method) && $method != "testcase") {  
	    $this->addTest(new $classname($method));
	  }
	}
      }
      else {
	$dummy = new $classname("dummy");
	$names = (array) $dummy;
	while (list($key, $value) = each($names)) {
	  $type = gettype($value);
	  if ($type == "user function" && preg_match('/^test/', $key)
	  && $key != "testcase") {  
	    $this->addTest(new $classname($key));
	  }
	}
      }
    }
  }

  function addTest($test) {
    /* Add TestCase or TestSuite to this TestSuite */
    $this->fTests[] = $test;
  }

  function run(&$testResult) {
    /* Run all TestCases and TestSuites comprising this TestSuite,
       accumulating results in the given TestResult object. */
    reset($this->fTests);
    while (list($na, $test) = each($this->fTests)) {
      if ($testResult->shouldStop())
	break;
      $test->run(&$testResult);
    }
  }

  function countTestCases() {
    /* Number of TestCases comprising this TestSuite (including those
       in any constituent TestSuites) */
    $count = 0;
    reset($fTests);
    while (list($na, $test_case) = each($this->fTests)) {
      $count += $test_case->countTestCases();
    }
    return $count;
  }
}


class TestFailure {
  /* Record failure of a single TestCase, associating it with the
     exception(s) that occurred */
  var $fFailedTestName;
  var $fExceptions;

  function TestFailure(&$test, &$exceptions) {
    $this->fFailedTestName = $test->name();
    $this->fExceptions = $exceptions;
  }

  function getExceptions() {
      return $this->fExceptions;
  }
  function getTestName() {
    return $this->fFailedTestName;
  }
}


class TestResult {
  /* Collect the results of running a set of TestCases. */
  var $fFailures = array();
  var $fRunTests = 0;
  var $fStop = false;

  function TestResult() { }

  function _endTest($test) /* protected */ {
      /* specialize this for end-of-test action, such as progress
	 reports  */
  }

  function getFailures() {
    return $this->fFailures;
  }

  function run($test) {
    /* Run a single TestCase in the context of this TestResult */
    $this->_startTest($test);
    $this->fRunTests++;

    $test->runBare();

    /* this is where JUnit would catch AssertionFailedError */
    $exceptions = $test->getExceptions();
    if ($exceptions)
      $this->fFailures[] = new TestFailure(&$test, &$exceptions);
    $this->_endTest($test);
  }

  function countTests() {
    return $this->fRunTests;
  }

  function shouldStop() {
    return $this->fStop;
  }

  function _startTest($test) /* protected */ {
      /* specialize this for start-of-test actions */
  }

  function stop() {
    /* set indication that the test sequence should halt */
    $fStop = true;
  }

  function countFailures() {
    return count($this->fFailures);
  }
}


class TextTestResult extends TestResult {
  /* Specialize TestResult to produce text/html report */
  function TextTestResult() {
    $this->TestResult();  // call superclass constructor
  }
  
  function report() {
    /* report result of test run */
    $nRun = $this->countTests();
    $nFailures = $this->countFailures();
    printf("<p>%s test%s run<br>", $nRun, ($nRun == 1) ? '' : 's');
    printf("%s failure%s.<br>\n", $nFailures, ($nFailures == 1) ? '' : 's');
    if ($nFailures == 0)
      return;

    print("<ol>\n");
    $failures = $this->getFailures();
    while (list($i, $failure) = each($failures)) {
      $failedTestName = $failure->getTestName();
      printf("<li>%s\n", $failedTestName);

      $exceptions = $failure->getExceptions();
      print("<ul>");
      while (list($na, $exception) = each($exceptions))
	printf("<li>%s\n", $exception->getMessage());
      print("</ul>");
    }
    print("</ol>\n");
  }

  function _startTest($test) {
    printf("%s ", $test->name());
    flush();
  }

  function _endTest($test) {
    $outcome = $test->failed()
       ? "<font color=\"red\">FAIL</font>"
       : "<font color=\"green\">ok</font>";
    printf("$outcome<br>\n");
    flush();
  }
}


class TestRunner {
  /* Run a suite of tests and report results. */
  function run($suite) {
    $result = new TextTestResult;
    $suite->run($result);
    $result->report();
  }
}

?>
