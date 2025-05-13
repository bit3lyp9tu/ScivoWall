import os
import sys
import datetime
import time

import unittest
import mypy
import re
from selenium import webdriver

from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.select import Select
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains

# from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.firefox.options import Options

# from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.firefox_binary import FirefoxBinary
from selenium.webdriver.firefox.service import Service as FirefoxService


class PythonOrgSearch(unittest.TestCase):

    wait_time = 1
    address = ""

    def setUp(self):
        options = Options()

        if os.environ.get("GITHUB_ACTIONS"):
            options.add_argument("--headless")

            options.binary_location = "/usr/bin/firefox"
            service = FirefoxService(
                executable_path="/home/runner/cache/.driver/geckodriver"
            )
            self.driver = webdriver.Firefox(service=service, options=options)
            self.address = "127.0.0.1:8080"
        else:
            self.driver = webdriver.Firefox(options=options)
            self.address = "localhost"

    def test_search_in_python_org(self):
        driver = self.driver
        driver.get(f"http://{self.address}/scientific_poster_generator/login.php")
        self.assertIn("Poster Generator", driver.title)
        print("Testing Page: " + driver.title)

        self.login_page(driver)

        self.user_page(driver)
        self.poster_page(driver, 3)
        self.admin_user(driver, "")
        self.index_page(driver)

        # self.logout(driver)

    def tearDown(self):
        if os.environ.get("GITHUB_ACTIONS"):
            self.driver.close()

    def login_page(self, driver):

        # check both empty
        self.login_fill_form(driver, "", "")
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        self.login_clear_form(self.driver)

        time.sleep(self.wait_time)
        # check wrong name
        self.login_fill_form(driver, "Max Mustermann" + "123", "AbC123-98xy")
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        self.login_clear_form(self.driver)

        time.sleep(self.wait_time)
        # check wrong pw
        self.login_fill_form(driver, "Max Mustermann", "AbC123-98xy" + "abc")
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        self.login_clear_form(self.driver)

        time.sleep(self.wait_time)
        # check right name+pw
        self.login_fill_form(driver, "max5", "abc")
        # time.sleep(self.wait_time)
        driver.find_element(By.ID, "login").click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )

        time.sleep(self.wait_time)
        # check cookie session
        session_id = driver.get_cookie("sessionID")
        time.sleep(self.wait_time)
        self.assertIsNotNone(session_id)

        pass

    def login_fill_form(self, driver, name, pw):
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)
        filed_name = driver.find_element(By.ID, "name")
        time.sleep(self.wait_time)
        filed_name.send_keys(name)
        filed_name.send_keys(Keys.RETURN)

        time.sleep(self.wait_time)

        filed_pw = driver.find_element(By.ID, "pw")
        time.sleep(self.wait_time)
        filed_pw.send_keys(pw)
        filed_pw.send_keys(Keys.RETURN)

    def login_clear_form(self, driver):
        driver.find_element(By.ID, "name").clear()
        time.sleep(self.wait_time)
        driver.find_element(By.ID, "pw").clear()

    def logout(self, driver):
        driver.get(f"http://{self.address}/scientific_poster_generator/projects.php")
        time.sleep(self.wait_time)
        # check if page contains logout
        logout = driver.find_element(By.ID, "logout")
        time.sleep(self.wait_time)
        self.assertIsNotNone(logout)
        time.sleep(self.wait_time)
        logout.click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )

        # check if correct logout   ???
        pass

    def register(self, driver, name, pw):
        # check existing name
        # check invalid pw
        # check two diffent pw
        # check correct action + now at login page
        pass

    def user_page(self, driver):
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)

        # check poster list correctly loaded
        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr#table-container--nr-3"
        )
        time.sleep(self.wait_time)
        self.assertTrue(
            poster_list_element.text
            in ["2025-04-16 13:43:02 Edit", "2025-04-16 11:43:02 Edit"]
        )

        time.sleep(self.wait_time)
        # check add new poster
        create_poster = driver.find_element(By.ID, "project-name")
        time.sleep(self.wait_time)
        create_poster.send_keys("Test Title")
        create_poster.send_keys(Keys.RETURN)

        time.sleep(self.wait_time)

        driver.find_element(By.CSS_SELECTOR, "#create-project>button").click()
        time.sleep(self.wait_time)

        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr#table-container--nr-4"
        )
        time.sleep(self.wait_time)
        self.assertIsNotNone(poster_list_element)

        # check right date
        date = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        time.sleep(self.wait_time)
        self.assertTrue(
            self.date_compair_day(date, poster_list_element.text.split(" ")[0])
        )

        time.sleep(self.wait_time)
        # check edit poster title
        custom_poster = driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-4>td:first-child>input",
        )
        time.sleep(self.wait_time)
        self.assertEqual("Test Title", custom_poster.get_attribute("value"))

        ActionChains(driver).click(custom_poster).send_keys(" abc").perform()

        # custom_poster.click()
        # custom_poster.send_keys(" abc")

        # time.sleep(3)
        # custom_poster.send_keys(Keys.TAB)
        time.sleep(3)

        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-1>td:first-child>input",
        ).click()
        time.sleep(3)

        # custom_poster2 = driver.find_element(
        #     By.CSS_SELECTOR,
        #     "#table-container>table>tr#table-container--nr-4>td:first-child>input",
        # )
        # self.assertEqual("Test Title abc", custom_poster2.get_attribute("value"))

        # check after page reload

        time.sleep(self.wait_time)
        # check delete new poster
        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-4>td:last-child>td>input",
        ).click()
        time.sleep(self.wait_time)
        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr:last-child>td:first-child>input"
        )
        time.sleep(self.wait_time)
        self.assertTrue(
            poster_list_element not in ["Test Title", "Test Title abc"]
        )  # TODO: change to only expecting 'Test Title abc'
        # self.assertEqual("Test Title abc", poster_list_element)

        time.sleep(self.wait_time)
        # check access poster
        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-3>td:nth-last-child(2)>td>a",
        ).click()
        time.sleep(self.wait_time)
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/poster.php",
            driver.current_url.split("?")[0],
        )

        driver.get(
            f"http://{self.address}/scientific_poster_generator/projects.php",
        )

        time.sleep(self.wait_time)

        # check author list correctly loaded
        author_list_element = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:first-child>input",
        )
        time.sleep(self.wait_time)
        self.assertEqual("Lina Chen", author_list_element.get_attribute("value"))

        author_list_element2 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:nth-child(2)",
        )
        time.sleep(self.wait_time)
        self.assertEqual("The Future of Urban Farming", author_list_element2.text)

        # check edit author name
        author_list_element3 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:first-child>input",
        )
        time.sleep(self.wait_time)
        author_list_element3.click()
        author_list_element3.send_keys(" abc")

        time.sleep(self.wait_time)
        author_list_element3.send_keys(Keys.TAB)
        time.sleep(self.wait_time)

        driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-1>td:first-child>input",
        ).click()
        time.sleep(self.wait_time)
        author_list_element4 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:first-child>input",
        )
        self.assertTrue(
            author_list_element4.get_attribute("value")
            in ["Lina Chen", "Lina Chen abc"]
        )  # TODO: change to only expecting 'Lina Chen abc'

        time.sleep(self.wait_time)
        # check delete author
        driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-6>td:last-child>td>input",
        ).click()
        time.sleep(self.wait_time)
        author_list_element5 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr:nth-child(6)>td:first-child>input",
        )
        self.assertIsNot("Alice johnson", author_list_element5.get_attribute("value"))

        # check image list  ???
        pass

    def date_compair_day(self, date1, date2):
        g1 = date1.split(" ")
        g2 = date2.split(" ")

        return g1[0] == g2[0]
        # and g1[1].split(":")[0] == g2[1].split(":")[0]

    def poster_page(self, driver, local_index):

        driver.get(
            f"http://{self.address}/scientific_poster_generator/projects.php",
        )

        time.sleep(3)

        driver.find_element(
            By.CSS_SELECTOR,
            f"#table-container>table>tr#table-container--nr-{local_index}>td:nth-last-child(2)>td>a",
        ).click()

        # check right title
        title = driver.find_element(By.CSS_SELECTOR, "div#title")
        time.sleep(self.wait_time)
        # print(title.text)
        self.assertEqual("The Future of Urban Farming", title.text)

        time.sleep(self.wait_time)

        # check edit title # TODO
        # driver.find_element(By.CSS_SELECTOR, "div#titles>div>div#title").click()
        # WebDriverWait(driver, 5).until(
        #     EC.element_to_be_clickable((By.CSS_SELECTOR, "div#titles>div>div#title"))
        # ).click()
        # title2 = driver.find_element(By.CSS_SELECTOR, "textearea#title").get_attribute(
        #     "data-content"
        # )
        # title2.click()
        # title2.send_keys(" abc")
        # title2.send_keys(Keys.RETURN)
        # title3 = driver.find_element(By.CSS_SELECTOR, "div#title>p")
        # self.assertEqual("The Future of Urban Farming", title3.text)
        # #   +globally

        time.sleep(self.wait_time)
        # check authors
        authors = set(
            [
                i.text
                for i in driver.find_elements(
                    By.CSS_SELECTOR, "div#typeahead-container>div.author-item"
                )
            ]
        )
        self.assertTrue(
            authors
            in [
                {"ChatGPT", "Alice Johnson", "Lina Chen abc"},
                {"ChatGPT", "Alice Johnson", "Lina Chen"},
            ]
        )

        # check empty authors

        # check add author
        WebDriverWait(driver, 20).until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, "div#typeahead-container>div:last-child")
            )
        ).click()
        ActionChains(driver).send_keys("Author").perform()
        driver.find_element(By.ID, "logo_headline").click()
        changed_authors = [
            i.text
            for i in driver.find_elements(
                By.CSS_SELECTOR, "div#typeahead-container>div.author-item"
            )
        ]
        # print(changed_authors)
        self.assertTrue(
            changed_authors
            in [
                ["ChatGPT", "Alice Johnson", "Lina Chen abc", "Author"],
                ["ChatGPT", "Alice Johnson", "Lina Chen", "Author"],
            ],
        )

        time.sleep(self.wait_time)
        # TODO: check author list switch order
        drag = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable(
                (
                    By.CSS_SELECTOR,
                    "div#typeahead-container>div:nth-child(1)",
                )
            )
        )
        start = drag.location
        time.sleep(self.wait_time)
        drop = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable(
                (
                    By.CSS_SELECTOR,
                    "div#typeahead-container>div:nth-child(3)",
                )
            )
        )
        finish = drop.location
        ActionChains(driver).drag_and_drop(drag, drop).perform()
        author_order = [
            i.text
            for i in driver.find_elements(
                By.CSS_SELECTOR, "div#typeahead-container>div.author-item"
            )
        ]
        # print(author_order)
        # self.assertEqual([], author_order)

        # check author stored

        # check author delete
        last_author = driver.find_element(
            By.CSS_SELECTOR, "div#typeahead-container>div:nth-last-child(2)"
        )
        ActionChains(driver).move_to_element(last_author).perform()
        driver.find_element(
            By.CSS_SELECTOR,
            "div#typeahead-container>div:nth-last-child(2)>button#remove-element",
        ).click()
        changed_authors2 = [
            i.text
            for i in driver.find_elements(
                By.CSS_SELECTOR, "div#typeahead-container>div.author-item"
            )
        ]
        self.assertTrue(
            changed_authors2
            in [
                ["ChatGPT", "Alice Johnson", "Lina Chen abc"],
                ["ChatGPT", "Alice Johnson", "Lina Chen"],
            ],
        )

        # check add box
        boxes = [i for i in driver.find_elements(By.CSS_SELECTOR, "div#boxes>div")]
        driver.find_element(By.CSS_SELECTOR, "button#add-box").click()
        boxes2 = [i for i in driver.find_elements(By.CSS_SELECTOR, "div#boxes>div")]
        self.assertEqual(len(boxes) + 1, len(boxes2))

        # check basic edit box
        new_box = driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)")
        ActionChains(driver).move_to_element(new_box).click(new_box).perform()

        ActionChains(driver).click(
            driver.find_element(By.CSS_SELECTOR, "div#boxes>textarea#editBox-2")
        ).send_keys(Keys.DOWN).send_keys(" abc").send_keys(Keys.ENTER).send_keys(
            "$$ x $$"
        ).click(
            driver.find_element(By.CSS_SELECTOR, "img#scadslogo")
        ).perform()

        changed_box = driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)")
        self.assertEqual(
            "# Impact\n\nIncreased yields with 70% less water usage. abc\n$$ x $$",
            changed_box.get_attribute("data-content"),
        )

        # check box markdown render
        self.assertEqual(
            "Impact",
            driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)>h1").text,
        )
        self.assertEqual(
            "Increased yields with 70% less water usage. abc\nx",
            driver.find_element(By.CSS_SELECTOR, "div#boxes>div:nth-child(3)>p").text,
        )

        # check box math render
        self.assertIsNotNone(
            driver.find_element(
                By.CSS_SELECTOR, "div#boxes>div:nth-child(3)>p>mjx-container"
            )
        )

        # TODO: check box plotly render   ???
        # TODO: check box image render    ???
        #   + stored globally

        # check visibility
        select_element = driver.find_element(By.CSS_SELECTOR, "select#view-mode")
        select = Select(select_element)
        # print([i.text for i in select.options])
        #   - private
        self.assertEqual("private", select.all_selected_options[0].text)
        #   - public
        select.select_by_visible_text("public")
        self.assertEqual(
            "public",
            Select(driver.find_element(By.CSS_SELECTOR, "select#view-mode"))
            .all_selected_options[0]
            .text,
        )
        driver.find_element(By.CSS_SELECTOR, "button#save-content").click()
        time.sleep(self.wait_time)

        driver.get(f"http://{self.address}/scientific_poster_generator/projects.php")
        time.sleep(self.wait_time)
        self.assertEqual(
            datetime.datetime.now().strftime("%Y-%m-%d"),
            driver.find_element(
                By.CSS_SELECTOR,
                "div#table-container>table>tr#table-container--nr-3",
            ).text.split(" ")[0],
        )

    def admin_user(self, driver, pw):
        # TODO: ??? check posters set on public

        # go to projects page
        driver.get(f"http://{self.address}/scientific_poster_generator/projects.php")
        time.sleep(self.wait_time)

        # logout
        driver.find_element(By.ID, "logout").click()
        time.sleep(self.wait_time)

        # login as admin
        self.login_fill_form(driver, "Admin", "PwScaDS-2025")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )
        time.sleep(self.wait_time)

        # check login successfully
        self.assertEqual(
            5,
            len(
                driver.find_elements(
                    By.CSS_SELECTOR,
                    "div#table-container>table>tr#table-container--nr-1>td",
                )
            ),
        )
        # check set poster to visible
        self.assertTrue(
            "on",
            driver.find_elements(
                By.CSS_SELECTOR,
                "div#table-container>table>tr#table-container--nr-1>td:nth-child(3)>input",
            )[0].get_attribute("value"),
        )
        driver.get(f"http://{self.address}/scientific_poster_generator/index.php")
        self.assertIsNotNone(
            driver.find_elements(By.CSS_SELECTOR, "div#posters>div>iframe")
        )

        time.sleep(self.wait_time)

        driver.get(f"http://{self.address}/scientific_poster_generator/projects.php")

        time.sleep(self.wait_time)

        visibility = driver.find_element(
            By.CSS_SELECTOR,
            "div#table-container>table>tr#table-container--nr-1>td:nth-child(3)>input",
        )

        # print(visibility.is_selected())

        ActionChains(driver).move_to_element(visibility).click(visibility).perform()

        # print(
        #     driver.find_element(
        #         By.CSS_SELECTOR,
        #         "div#table-container>table>tr#table-container--nr-1>td:nth-child(3)",
        #     ).is_selected()
        # )

        time.sleep(self.wait_time)

        driver.get(f"http://{self.address}/scientific_poster_generator/index.php")

        time.sleep(self.wait_time)

        self.assertEqual(
            [], driver.find_elements(By.CSS_SELECTOR, "div#posters>div>iframe")
        )

    def index_page(self, driver):
        # check poster count
        # check poster#1 content
        # check prevent editing:
        #   -title
        #   -add author
        #   -author item interaction
        #   -change author order
        #   -box text
        #   -box image drop
        #   -box interaction
        pass


if __name__ == "__main__":
    unittest.main()

    # TODO: email notification test
