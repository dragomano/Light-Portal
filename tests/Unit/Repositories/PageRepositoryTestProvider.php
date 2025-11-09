<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;

class PageRepositoryTestProvider
{
    public function generatePrevNextTestData(): array
    {
        User::$me = new User(1);
        User::$me->language = 'english';
        User::$me->groups = [0];

        Utils::$smcFunc['strtolower'] = 'strtolower';

        $commentCounter = 1001;
        $mockData = [
            [
                'id'              => 1,
                'category_id'     => 0,
                'author_id'       => 1,
                'author'          => 'Alice Johnson',
                'slug'            => 'page-one',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 0,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 100,
                'num_comments'    => 5,
                'created_at'      => 1698796800, // 2023-10-31
                'updated_at'      => 1700000000, // later
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1700000000,
                'image'           => 'https://example.com/image1.jpg',
                'title'           => 'Alpha Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description one.',
                'options'         => [],
            ],
            [
                'id'              => 2,
                'category_id'     => 1,
                'author_id'       => 2,
                'author'          => 'Bob Smith',
                'slug'            => 'page-two',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 1,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 200,
                'num_comments'    => 0,
                'created_at'      => 1696204800, // earlier
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1696204800,
                'image'           => 'https://example.com/image2.jpg',
                'title'           => 'Beta Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description two.',
                'options'         => [],
            ],
            [
                'id'              => 3,
                'category_id'     => 0,
                'author_id'       => 3,
                'author'          => 'Charlie Brown',
                'slug'            => 'page-three',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 2,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 300,
                'num_comments'    => 10,
                'created_at'      => 1702476800, // later
                'updated_at'      => 1702476800,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1703000000,
                'image'           => 'https://example.com/image3.jpg',
                'title'           => 'Charlie Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description three.',
                'options'         => [],
            ],
            [
                'id'              => 4,
                'category_id'     => 2,
                'author_id'       => 4,
                'author'          => 'Diana Prince',
                'slug'            => 'page-four',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 3,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 50,
                'num_comments'    => 2,
                'created_at'      => 1698796800,
                'updated_at'      => 1701000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1701000000,
                'image'           => 'https://example.com/image4.jpg',
                'title'           => 'Delta Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description four.',
                'options'         => [],
            ],
            [
                'id'              => 5,
                'category_id'     => 0,
                'author_id'       => 5,
                'author'          => 'Eve Davis',
                'slug'            => 'page-five',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 4,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 1500,
                'num_comments'    => 0,
                'created_at'      => 1693612800, // much earlier
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1693612800,
                'image'           => 'https://example.com/image5.jpg',
                'title'           => 'Echo Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description five.',
                'options'         => [],
            ],
            [
                'id'              => 6,
                'category_id'     => 3,
                'author_id'       => 6,
                'author'          => 'Frank Wilson',
                'slug'            => 'page-six',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 5,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 5000,
                'num_comments'    => 15,
                'created_at'      => 1700000000,
                'updated_at'      => 1705000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1705000000,
                'image'           => 'https://example.com/image6.jpg',
                'title'           => 'Foxtrot Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description six.',
                'options'         => [],
            ],
            [
                'id'              => 7,
                'category_id'     => 1,
                'author_id'       => 7,
                'author'          => 'Grace Lee',
                'slug'            => 'page-seven',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 0,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 250,
                'num_comments'    => 3,
                'created_at'      => 1696204800,
                'updated_at'      => 1697000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1697000000,
                'image'           => 'https://example.com/image7.jpg',
                'title'           => 'Golf Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description seven.',
                'options'         => [],
            ],
            [
                'id'              => 8,
                'category_id'     => 4,
                'author_id'       => 8,
                'author'          => 'Henry Miller',
                'slug'            => 'page-eight',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 6,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 75,
                'num_comments'    => 0,
                'created_at'      => 1702476800,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1702476800,
                'image'           => 'https://example.com/image8.jpg',
                'title'           => 'Hotel Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description eight.',
                'options'         => [],
            ],
            [
                'id'              => 9,
                'category_id'     => 2,
                'author_id'       => 9,
                'author'          => 'Ivy Chen',
                'slug'            => 'page-nine',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 1,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 8000,
                'num_comments'    => 20,
                'created_at'      => 1698796800,
                'updated_at'      => 1700000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1700000000,
                'image'           => 'https://example.com/image9.jpg',
                'title'           => 'India Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description nine.',
                'options'         => [],
            ],
            [
                'id'              => 10,
                'category_id'     => 0,
                'author_id'       => 10,
                'author'          => 'Jack Thompson',
                'slug'            => 'page-ten',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 2,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 1200,
                'num_comments'    => 1,
                'created_at'      => 1701000000,
                'updated_at'      => 1701000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1701500000,
                'image'           => 'https://example.com/image10.jpg',
                'title'           => 'Juliet Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description ten.',
                'options'         => [],
            ],
            // Continuing with more varied data for pages 11-36 to ensure coverage across categories, sorts, etc.
            [
                'id'              => 11,
                'category_id'     => 5,
                'author_id'       => 11,
                'author'          => 'Kara Kent',
                'slug'            => 'page-eleven',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 3,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 300,
                'num_comments'    => 8,
                'created_at'      => 1693612800,
                'updated_at'      => 1695000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1695000000,
                'image'           => 'https://example.com/image11.jpg',
                'title'           => 'Kilo Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description eleven.',
                'options'         => [],
            ],
            [
                'id'              => 12,
                'category_id'     => 6,
                'author_id'       => 12,
                'author'          => 'Leo Garcia',
                'slug'            => 'page-twelve',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 4,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 4500,
                'num_comments'    => 0,
                'created_at'      => 1705000000,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1705000000,
                'image'           => 'https://example.com/image12.jpg',
                'title'           => 'Lima Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twelve.',
                'options'         => [],
            ],
            [
                'id'              => 13,
                'category_id'     => 1,
                'author_id'       => 13,
                'author'          => 'Mia Rodriguez',
                'slug'            => 'page-thirteen',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 5,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 600,
                'num_comments'    => 12,
                'created_at'      => 1697000000,
                'updated_at'      => 1698000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1698000000,
                'image'           => 'https://example.com/image13.jpg',
                'title'           => 'Mike Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirteen.',
                'options'         => [],
            ],
            [
                'id'              => 14,
                'category_id'     => 7,
                'author_id'       => 14,
                'author'          => 'Noah Kim',
                'slug'            => 'page-fourteen',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 0,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 10000,
                'num_comments'    => 4,
                'created_at'      => 1702476800,
                'updated_at'      => 1703000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1703000000,
                'image'           => 'https://example.com/image14.jpg',
                'title'           => 'November Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description fourteen.',
                'options'         => [],
            ],
            [
                'id'              => 15,
                'category_id'     => 3,
                'author_id'       => 15,
                'author'          => 'Olivia Martinez',
                'slug'            => 'page-fifteen',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 1,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 20,
                'num_comments'    => 0,
                'created_at'      => 1690000000,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1690000000,
                'image'           => 'https://example.com/image15.jpg',
                'title'           => 'Oscar Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description fifteen.',
                'options'         => [],
            ],
            [
                'id'              => 16,
                'category_id'     => 8,
                'author_id'       => 16,
                'author'          => 'Paul Walker',
                'slug'            => 'page-sixteen',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 2,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 3500,
                'num_comments'    => 25,
                'created_at'      => 1700000000,
                'updated_at'      => 1701000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1701000000,
                'image'           => 'https://example.com/image16.jpg',
                'title'           => 'Papa Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description sixteen.',
                'options'         => [],
            ],
            [
                'id'              => 17,
                'category_id'     => 4,
                'author_id'       => 17,
                'author'          => 'Quinn Hall',
                'slug'            => 'page-seventeen',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 3,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 400,
                'num_comments'    => 6,
                'created_at'      => 1696204800,
                'updated_at'      => 1696204800,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1696500000,
                'image'           => 'https://example.com/image17.jpg',
                'title'           => 'Quebec Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description seventeen.',
                'options'         => [],
            ],
            [
                'id'              => 18,
                'category_id'     => 9,
                'author_id'       => 18,
                'author'          => 'Riley Adams',
                'slug'            => 'page-eighteen',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 4,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 900,
                'num_comments'    => 0,
                'created_at'      => 1705000000,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1705000000,
                'image'           => 'https://example.com/image18.jpg',
                'title'           => 'Romeo Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description eighteen.',
                'options'         => [],
            ],
            [
                'id'              => 19,
                'category_id'     => 2,
                'author_id'       => 19,
                'author'          => 'Sophia Baker',
                'slug'            => 'page-nineteen',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 5,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 7000,
                'num_comments'    => 18,
                'created_at'      => 1698796800,
                'updated_at'      => 1700000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1700000000,
                'image'           => 'https://example.com/image19.jpg',
                'title'           => 'Sierra Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description nineteen.',
                'options'         => [],
            ],
            [
                'id'              => 20,
                'category_id'     => 10,
                'author_id'       => 20,
                'author'          => 'Tyler Clark',
                'slug'            => 'page-twenty',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 6,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 150,
                'num_comments'    => 7,
                'created_at'      => 1702476800,
                'updated_at'      => 1702476800,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1702800000,
                'image'           => 'https://example.com/image20.jpg',
                'title'           => 'Tango Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty.',
                'options'         => [],
            ],
            [
                'id'              => 21,
                'category_id'     => 5,
                'author_id'       => 21,
                'author'          => 'Uma Patel',
                'slug'            => 'page-twenty-one',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 0,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 2500,
                'num_comments'    => 0,
                'created_at'      => 1693612800,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1693612800,
                'image'           => 'https://example.com/image21.jpg',
                'title'           => 'Uniform Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-one.',
                'options'         => [],
            ],
            [
                'id'              => 22,
                'category_id'     => 6,
                'author_id'       => 22,
                'author'          => 'Victor Nguyen',
                'slug'            => 'page-twenty-two',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 1,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 550,
                'num_comments'    => 22,
                'created_at'      => 1700000000,
                'updated_at'      => 1702000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1702000000,
                'image'           => 'https://example.com/image22.jpg',
                'title'           => 'Victor Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-two.',
                'options'         => [],
            ],
            [
                'id'              => 23,
                'category_id'     => 7,
                'author_id'       => 23,
                'author'          => 'Wendy Scott',
                'slug'            => 'page-twenty-three',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 2,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 800,
                'num_comments'    => 9,
                'created_at'      => 1696204800,
                'updated_at'      => 1697000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1697000000,
                'image'           => 'https://example.com/image23.jpg',
                'title'           => 'Whiskey Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-three.',
                'options'         => [],
            ],
            [
                'id'              => 24,
                'category_id'     => 8,
                'author_id'       => 24,
                'author'          => 'Xander Young',
                'slug'            => 'page-twenty-four',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 3,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 12000, // >10000 but ok
                'num_comments'    => 0,
                'created_at'      => 1702476800,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1702476800,
                'image'           => 'https://example.com/image24.jpg',
                'title'           => 'Xray Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-four.',
                'options'         => [],
            ],
            [
                'id'              => 25,
                'category_id'     => 9,
                'author_id'       => 25,
                'author'          => 'Yara Lopez',
                'slug'            => 'page-twenty-five',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 4,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 650,
                'num_comments'    => 30,
                'created_at'      => 1698796800,
                'updated_at'      => 1700000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1700000000,
                'image'           => 'https://example.com/image25.jpg',
                'title'           => 'Yankee Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-five.',
                'options'         => [],
            ],
            [
                'id'              => 26,
                'category_id'     => 10,
                'author_id'       => 26,
                'author'          => 'Simon Evans',
                'slug'            => 'page-twenty-six',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 5,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 2000,
                'num_comments'    => 11,
                'created_at'      => 1701000000,
                'updated_at'      => 1701000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1701500000,
                'image'           => 'https://example.com/image26.jpg',
                'title'           => 'Zulu Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-six.',
                'options'         => [],
            ],
            [
                'id'              => 27,
                'category_id'     => 0,
                'author_id'       => 27,
                'author'          => 'Anna White',
                'slug'            => 'page-twenty-seven',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 6,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 100,
                'num_comments'    => 0,
                'created_at'      => 1693612800,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1693612800,
                'image'           => 'https://example.com/image27.jpg',
                'title'           => 'Apple Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-seven.',
                'options'         => [],
            ],
            [
                'id'              => 28,
                'category_id'     => 1,
                'author_id'       => 28,
                'author'          => 'Ben Green',
                'slug'            => 'page-twenty-eight',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 0,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 4000,
                'num_comments'    => 14,
                'created_at'      => 1700000000,
                'updated_at'      => 1704000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1704000000,
                'image'           => 'https://example.com/image28.jpg',
                'title'           => 'Banana Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-eight.',
                'options'         => [],
            ],
            [
                'id'              => 29,
                'category_id'     => 2,
                'author_id'       => 29,
                'author'          => 'Cara Black',
                'slug'            => 'page-twenty-nine',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 1,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 300,
                'num_comments'    => 2,
                'created_at'      => 1696204800,
                'updated_at'      => 1696204800,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1696204800,
                'image'           => 'https://example.com/image29.jpg',
                'title'           => 'Cherry Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description twenty-nine.',
                'options'         => [],
            ],
            [
                'id'              => 30,
                'category_id'     => 3,
                'author_id'       => 30,
                'author'          => 'David Red',
                'slug'            => 'page-thirty',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 2,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 8500,
                'num_comments'    => 0,
                'created_at'      => 1702476800,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1702476800,
                'image'           => 'https://example.com/image30.jpg',
                'title'           => 'Date Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirty.',
                'options'         => [],
            ],
            [
                'id'              => 31,
                'category_id'     => 4,
                'author_id'       => 31,
                'author'          => 'Emma Blue',
                'slug'            => 'page-thirty-one',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 3,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 500,
                'num_comments'    => 16,
                'created_at'      => 1698796800,
                'updated_at'      => 1700000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1700000000,
                'image'           => 'https://example.com/image31.jpg',
                'title'           => 'Elderberry Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirty-one.',
                'options'         => [],
            ],
            [
                'id'              => 32,
                'category_id'     => 5,
                'author_id'       => 32,
                'author'          => 'Finn Gray',
                'slug'            => 'page-thirty-two',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 4,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 1100,
                'num_comments'    => 5,
                'created_at'      => 1701000000,
                'updated_at'      => 1701000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1701500000,
                'image'           => 'https://example.com/image32.jpg',
                'title'           => 'Fig Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirty-two.',
                'options'         => [],
            ],
            [
                'id'              => 33,
                'category_id'     => 6,
                'author_id'       => 33,
                'author'          => 'Gina Yellow',
                'slug'            => 'page-thirty-three',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 5,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 250,
                'num_comments'    => 0,
                'created_at'      => 1693612800,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1693612800,
                'image'           => 'https://example.com/image33.jpg',
                'title'           => 'Grape Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirty-three.',
                'options'         => [],
            ],
            [
                'id'              => 34,
                'category_id'     => 7,
                'author_id'       => 34,
                'author'          => 'Hugo Orange',
                'slug'            => 'page-thirty-four',
                'type'            => 'bbc',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 6,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 6000,
                'num_comments'    => 19,
                'created_at'      => 1700000000,
                'updated_at'      => 1703000000,
                'last_comment_id' => $commentCounter++,
                'sort_value'      => 1703000000,
                'image'           => 'https://example.com/image34.jpg',
                'title'           => 'Guava Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirty-four.',
                'options'         => [],
            ],
            [
                'id'              => 35,
                'category_id'     => 8,
                'author_id'       => 35,
                'author'          => 'Iris Purple',
                'slug'            => 'page-thirty-five',
                'type'            => 'html',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 0,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 950,
                'num_comments'    => 3,
                'created_at'      => 1696204800,
                'updated_at'      => 1697000000,
                'last_comment_id' => $commentCounter,
                'sort_value'      => 1697000000,
                'image'           => 'https://example.com/image35.jpg',
                'title'           => 'Honeydew Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirty-five.',
                'options'         => [],
            ],
            [
                'id'              => 36,
                'category_id'     => 9,
                'author_id'       => 36,
                'author'          => 'Jasper Pink',
                'slug'            => 'page-thirty-six',
                'type'            => 'php',
                'entry_type'      => EntryType::DEFAULT->name(),
                'permissions'     => 1,
                'status'          => Status::ACTIVE->value,
                'num_views'       => 3200,
                'num_comments'    => 0,
                'created_at'      => 1702476800,
                'updated_at'      => 0,
                'last_comment_id' => 0,
                'sort_value'      => 1702476800,
                'image'           => 'https://example.com/image36.jpg',
                'title'           => 'Kiwi Page',
                'content'         => 'First paragraph content. Second paragraph content.',
                'description'     => 'Description thirty-six.',
                'options'         => [],
            ],
        ];

        $allowedCategories = range(0, 10);

        $cmpMap = [
            'created;desc' => fn($a, $b) => $b['created_at'] <=> $a['created_at']
                ?: $b['created_at'] <=> $a['created_at']
                    ?: $b['id'] <=> $a['id'],

            'created' => fn($a, $b) => $a['created_at'] <=> $b['created_at']
                ?: $a['created_at'] <=> $b['created_at']
                    ?: $a['id'] <=> $b['id'],

            'updated;desc' => fn($a, $b) => max($b['created_at'], $b['updated_at']) <=> max($a['created_at'], $a['updated_at'])
                ?: $b['created_at'] <=> $a['created_at']
                    ?: $b['id'] <=> $a['id'],

            'updated' => fn($a, $b) => max($a['created_at'], $a['updated_at']) <=> max($b['created_at'], $b['updated_at'])
                ?: $a['created_at'] <=> $b['created_at']
                    ?: $a['id'] <=> $b['id'],

            'last_comment;desc' => fn($a, $b) => $b['sort_value'] <=> $a['sort_value']
                ?: $b['created_at'] <=> $a['created_at']
                    ?: $b['id'] <=> $a['id'],

            'last_comment' => fn($a, $b) => $a['sort_value'] <=> $b['sort_value']
                ?: $a['created_at'] <=> $b['created_at']
                    ?: $a['id'] <=> $b['id'],

            'title;desc' => fn($a, $b) => Utils::$smcFunc['strtolower']($b['title']) <=> Utils::$smcFunc['strtolower']($a['title'])
                ?: $b['created_at'] <=> $a['created_at']
                    ?: $b['id'] <=> $a['id'],

            'title' => fn($a, $b) => Utils::$smcFunc['strtolower']($a['title']) <=> Utils::$smcFunc['strtolower']($b['title'])
                ?: $a['created_at'] <=> $b['created_at']
                    ?: $a['id'] <=> $b['id'],

            'author_name;desc' => fn($a, $b) => Utils::$smcFunc['strtolower']($b['author']) <=> Utils::$smcFunc['strtolower']($a['author'])
                ?: $b['created_at'] <=> $a['created_at']
                    ?: $b['id'] <=> $a['id'],

            'author_name' => fn($a, $b) => Utils::$smcFunc['strtolower']($a['author']) <=> Utils::$smcFunc['strtolower']($b['author'])
                ?: $a['created_at'] <=> $b['created_at']
                    ?: $a['id'] <=> $b['id'],

            'num_views;desc' => fn($a, $b) => $b['num_views'] <=> $a['num_views']
                ?: $b['created_at'] <=> $a['created_at']
                    ?: $b['id'] <=> $a['id'],

            'num_views' => fn($a, $b) => $a['num_views'] <=> $b['num_views']
                ?: $a['created_at'] <=> $b['created_at']
                    ?: $a['id'] <=> $b['id'],

            'num_replies;desc' => fn($a, $b) => $b['num_comments'] <=> $a['num_comments']
                ?: $b['created_at'] <=> $a['created_at']
                    ?: $b['id'] <=> $a['id'],

            'num_replies' => fn($a, $b) => $a['num_comments'] <=> $b['num_comments']
                ?: $a['created_at'] <=> $b['created_at']
                    ?: $a['id'] <=> $b['id'],
        ];

        $filteredAll = array_filter($mockData, function ($item) use ($allowedCategories) {
            if ($item['created_at'] > time()) {
                return false;
            }

            if ($item['entry_type'] != EntryType::DEFAULT->name()) {
                return false;
            }

            if ($item['status'] !== Status::ACTIVE->value) {
                return false;
            }

            if (! in_array($item['permissions'], Permission::all())) {
                return false;
            }

            if (! in_array($item['category_id'], $allowedCategories)) {
                return false;
            }

            if (empty($item['title'])) {
                return false;
            }

            return true;
        });

        $filteredAll = array_values($filteredAll);
        $selectedPages = array_slice($filteredAll, 0, 10);
        shuffle($selectedPages);
        $selectedPage = $selectedPages[0];

        $sortingTypes = [
            'created;desc', 'created',
            'updated;desc', 'updated',
            'last_comment;desc', 'last_comment',
            'title;desc', 'title',
            'author_name;desc', 'author_name',
            'num_views;desc', 'num_views',
            'num_replies;desc', 'num_replies',
        ];

        $dataset = [];
        foreach ($sortingTypes as $sorting) {
            foreach ([true, false] as $withinCategory) {
                $cmp = $cmpMap[$sorting];

                $pagesForSorting = $selectedPages;
                if ($withinCategory) {
                    $pagesForSorting = array_filter(
                        $pagesForSorting,
                        fn($p) => $p['category_id'] == $selectedPage['category_id']
                    );
                }
                usort($pagesForSorting, $cmp);

                $compare = function($a, $b, $op) {
                    return match($op) {
                        '>' => $a > $b,
                        '<' => $a < $b,
                        '==' => $a == $b,
                        default => false,
                    };
                };

                $listAsc = ! str_contains($sorting, ';desc');
                $nextPrimaryOp = $listAsc ? '>' : '<';
                $nextSecondaryOp = $listAsc ? '>' : '<';
                $prevPrimaryOp = $listAsc ? '<' : '>';
                $prevSecondaryOp = $listAsc ? '<' : '>';

                $currentPrimary = match (true) {
                    str_contains($sorting, 'updated')      => max($selectedPage['created_at'], $selectedPage['updated_at']),
                    str_contains($sorting, 'last_comment') => $selectedPage['sort_value'] ?? $selectedPage['created_at'],
                    str_contains($sorting, 'title')        => Utils::$smcFunc['strtolower']($selectedPage['title']),
                    str_contains($sorting, 'author_name')  => Utils::$smcFunc['strtolower']($selectedPage['author']),
                    str_contains($sorting, 'num_views')    => $selectedPage['num_views'],
                    str_contains($sorting, 'num_replies')  => $selectedPage['num_comments'],
                    default => $selectedPage['created_at'],
                };
                $currentSecondary = $selectedPage['created_at'];

                $getItemPrimary = function($item, $sorting) {
                    return match (true) {
                        str_contains($sorting, 'updated')      => max($item['created_at'], $item['updated_at']),
                        str_contains($sorting, 'last_comment') => $item['sort_value'] ?? $item['created_at'],
                        str_contains($sorting, 'title')        => Utils::$smcFunc['strtolower']($item['title']),
                        str_contains($sorting, 'author_name')  => Utils::$smcFunc['strtolower']($item['author']),
                        str_contains($sorting, 'num_views')    => $item['num_views'],
                        str_contains($sorting, 'num_replies')  => $item['num_comments'],
                        default => $item['created_at'],
                    };
                };

                $nextWhere = function ($item) use (
                    $currentPrimary,
                    $currentSecondary,
                    $nextPrimaryOp,
                    $nextSecondaryOp,
                    $getItemPrimary,
                    $sorting,
                    $compare
                ) {
                    $itemPrimary = $getItemPrimary($item, $sorting);

                    return $compare($itemPrimary, $currentPrimary, $nextPrimaryOp) ||
                        ($itemPrimary == $currentPrimary && $compare($item['created_at'], $currentSecondary, $nextSecondaryOp));
                };

                $prevWhere = function ($item) use (
                    $currentPrimary,
                    $currentSecondary,
                    $prevPrimaryOp,
                    $prevSecondaryOp,
                    $getItemPrimary,
                    $sorting,
                    $compare
                ) {
                    $itemPrimary = $getItemPrimary($item, $sorting);

                    return $compare($itemPrimary, $currentPrimary, $prevPrimaryOp) ||
                        ($itemPrimary == $currentPrimary && $compare($item['created_at'], $currentSecondary, $prevSecondaryOp));
                };

                $candidates = array_filter($pagesForSorting, fn($p) => $p['id'] != $selectedPage['id']);

                $nextCandidates = array_filter($candidates, $nextWhere);
                usort($nextCandidates, $cmp);
                $next = ! empty($nextCandidates) ? $nextCandidates[0] : null;

                $prevCmp = function($a, $b) use ($cmp) { return $cmp($b, $a); };
                $prevCandidates = array_filter($candidates, $prevWhere);
                usort($prevCandidates, $prevCmp);
                $prev = ! empty($prevCandidates) ? $prevCandidates[0] : null;

                if (empty($next) && empty($prev)) {
                    continue;
                }

                $dataset[] = [
                    $sorting,
                    $selectedPage['id'],
                    $prev['title'] ?? '',
                    $prev['slug'] ?? '',
                    $prev['id'] ?? 0,
                    $next['title'] ?? '',
                    $next['slug'] ?? '',
                    $next['id'] ?? 0,
                    $withinCategory,
                    $pagesForSorting,
                ];
            }
        }

        return [$dataset, $mockData, $cmpMap];
    }
}
