<?php

use Kispiox\Authentication\UsernamePasswordHandler;
use Kispiox\Authentication\UsernamePasswordRequest;

class UsernamePasswordHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $request = $this->createMock(UsernamePasswordRequest::class);
        $request->expects($this->exactly(2))
            ->method('getUsername')
            ->willReturn('foo');
        $request->expects($this->exactly(2))
            ->method('getPassword')
            ->will($this->onConsecutiveCalls('bar', 'baz'));

        $users = [
            'foo' => password_hash('bar', PASSWORD_BCRYPT)
        ];

        $handler = new UsernamePasswordHandler($users);
        $response = $handler->handleUsernamePassword($request);
        $attr = $response->getAttributes();
        $this->assertTrue($response->isValid());
        $this->assertEquals($attr['user'], 'foo');

        $response = $handler->handleUsernamePassword($request);
        $this->assertFalse($response->isValid());
    }
}

