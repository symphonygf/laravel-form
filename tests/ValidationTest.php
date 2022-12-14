<?php

namespace Yobidoyobi\Form\Tests;

use Yobidoyobi\Form\Facade\FormFactory;
use Yobidoyobi\Form\Tests\Types\ParentFormType;
use Yobidoyobi\Form\Tests\Types\UserFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ValidationTest extends TestCase
{
    public function testValidForm()
    {
        /** @var Form $form */
        $form = FormFactory::create(UserFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save user']);

        $request = $this->createPostRequest([
            'user_form' => [
                'name' => 'Barry',
                'email' => 'barry@example.com',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
    }

    public function testMissingNameForm()
    {
        /** @var Form $form */
        $form = FormFactory::create(UserFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save user']);

        $request = $this->createPostRequest([
            'user_form' => [
                'email' => 'barry@example.com',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('The name field is required.', $form->getErrors(true)[0]->getMessage());
    }

    public function testInvalidEmailForm()
    {
        /** @var Form $form */
        $form = FormFactory::create(UserFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save user']);

        $request = $this->createPostRequest([
            'user_form' => [
                'name' => 'Barry',
                'email' => 'foo',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('The email must be a valid email address.', $form->getErrors(true)[0]->getMessage());
    }

    public function testEmptyCollectionForm()
    {
        /** @var Form $form */
        $form = FormFactory::create(ParentFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save parent']);

        $request = $this->createPostRequest([
            'parent_form' => [
                'name' => 'Barry',
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('The children field is required.', $form->getErrors(true)[0]->getMessage());
    }

    public function testInvalidCollectionForm()
    {
        /** @var Form $form */
        $form = FormFactory::create(ParentFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save parent']);

        $request = $this->createPostRequest([
            'parent_form' => [
                'name' => 'Barry',
                'children' => [
                    [
                        'name' => 'Foo',
                        'email' => 'foo@example.com',
                    ],
                    [
                        'name' => 'Bar',
                        'email' => 'bar',
                    ]
                ],
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('The children.1.email must be a valid email address.', $form->getErrors(true)[0]->getMessage());
    }

    public function testValidCollectionForm()
    {
        /** @var Form $form */
        $form = FormFactory::create(ParentFormType::class, [])
            ->add('save', SubmitType::class, ['label' => 'Save parent']);

        $request = $this->createPostRequest([
            'parent_form' => [
                'name' => 'Barry',
                'children' => [
                    [
                        'name' => 'Foo',
                        'email' => 'foo@example.com',
                    ],
                    [
                        'name' => 'Bar',
                        'email' => 'bar@example.com',
                    ]
                ],
                'save' => true,
            ]
        ]);

        $form->handleRequest($request);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
    }

    private function createPostRequest($data): Request
    {
        return new Request([], $data, [], [], [], [
            'REQUEST_METHOD' => 'POST'
        ]);
    }
}
