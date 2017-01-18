<?php

use Kispiox\Authentication\TokenManipulator;
use MattFerris\Configuration\ConfigurationInterface;
use MattFerris\Auth\ResponseInterface;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class TokenManipulatorTest extends PHPUnit_Framework_TestCase
{
    public function testManipulate()
    {
        $config = $this->createMock(ConfigurationInterface::class);

        $config->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['app.auth.duration'], ['app.auth.key'])
            ->will($this->onConsecutiveCalls(true, true));

        $config->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['app.auth.duration'], ['app.auth.key'])
            ->will($this->onConsecutiveCalls(3600, 'foo'));

        $response = $this->createMock(ResponseInterface::class);

        $response->expects($this->exactly(2))
            ->method('isValid')
            ->willReturn(true);

        $response->expects($this->once())
            ->method('getAttributes')
            ->willReturn([
                'foo' => 'bar'
            ]);

        $manipulator = new TokenManipulator($config);
        $resp = $manipulator->manipulate($response);
        $attr = $resp->getAttributes();
        $token = $attr['token'];

        $this->assertTrue($resp->isValid());
        $this->assertTrue($token->verify(new Sha256(), 'foo'));
    }
}

