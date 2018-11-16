<?php
/**
 * This file is part of DoctrineRestDriver.
 *
 * DoctrineRestDriver is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DoctrineRestDriver is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DoctrineRestDriver.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Circle\DoctrineRestDriver\Tests\Factory;

use Circle\DoctrineRestDriver\Factory\ResponseExceptionFactory;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Driver\DriverException as DriverExceptionInterface;
use Doctrine\DBAL\Exception as DBALException;

/**
 * @coversDefaultClass Circle\DoctrineRestDriver\Factory\ResponseExceptionFactory
 * 
 * @author Rob Treacy <robert.treacy@thesalegroup.co.uk>
 */
class ResponseExceptionFactoryTest extends \PHPUnit\Framework\TestCase {
    /**
     * @var ResponseExceptionFactory
     */
    private $responseExceptionFactory;
    
    public function setUp() {
        $this->responseExceptionFactory = new ResponseExceptionFactory;
    }
    
    /**
     * @test
     * @group  unit
     * @covers ::createDbalException
     * @dataProvider createDbalExceptionProvider
     */
    public function createDbalException(Response $response, $expectedExceptionClass) {
        $return = $this->responseExceptionFactory->createDbalException($response, $this->createMock($expectedExceptionClass));
        $this->assertInstanceOf(DriverExceptionInterface::class, $return);
    }
    
    /**
     * Data provider for createDbalException test
     */
    public function createDbalExceptionProvider() {
        return array(
            [$this->createResponseFromCode(Response::HTTP_BAD_REQUEST), DBALException\SyntaxErrorException::class],
            [$this->createResponseFromCode(Response::HTTP_METHOD_NOT_ALLOWED), DBALException\ServerException::class],
            [$this->createResponseFromCode(Response::HTTP_NOT_ACCEPTABLE), DBALException\ServerException::class],
            [$this->createResponseFromCode(Response::HTTP_REQUEST_TIMEOUT), DBALException\ServerException::class],
            [$this->createResponseFromCode(Response::HTTP_LENGTH_REQUIRED), DBALException\ServerException::class],
            [$this->createResponseFromCode(Response::HTTP_INTERNAL_SERVER_ERROR), DBALException\ServerException::class],
            [$this->createResponseFromCode(Response::HTTP_CONFLICT), DBALException\ConstraintViolationException::class],
            [$this->createResponseFromCode(Response::HTTP_UNAUTHORIZED), DBALException\ConnectionException::class],
            [$this->createResponseFromCode(Response::HTTP_FORBIDDEN), DBALException\ConnectionException::class],
            [$this->createResponseFromCode(Response::HTTP_PROXY_AUTHENTICATION_REQUIRED), DBALException\ConnectionException::class],
            [$this->createResponseFromCode(Response::HTTP_BAD_GATEWAY), DBALException\ConnectionException::class],
            [$this->createResponseFromCode(Response::HTTP_SERVICE_UNAVAILABLE), DBALException\ConnectionException::class],
            [$this->createResponseFromCode(Response::HTTP_GATEWAY_TIMEOUT), DBALException\ConnectionException::class],
            [$this->createResponseFromCode(Response::HTTP_OK), DBALException\DriverException::class],
        );
    }
    
    private function createResponseFromCode($responseCode) {
        return new Response('', $responseCode);
    }
}
